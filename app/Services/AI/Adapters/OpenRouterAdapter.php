<?php

namespace App\Services\AI\Adapters;

use App\Services\AI\AIServiceInterface;
use Illuminate\Support\Facades\Log;
use MoeMizrak\LaravelOpenrouter\DTO\ChatData;
use MoeMizrak\LaravelOpenrouter\DTO\ImageContentPartData;
use MoeMizrak\LaravelOpenrouter\DTO\ImageUrlData;
use MoeMizrak\LaravelOpenrouter\DTO\MessageData;
use MoeMizrak\LaravelOpenrouter\DTO\ProviderPreferencesData;
use MoeMizrak\LaravelOpenrouter\DTO\ResponseFormatData;
use MoeMizrak\LaravelOpenrouter\DTO\TextContentData;
use MoeMizrak\LaravelOpenRouter\Facades\LaravelOpenRouter;
use Illuminate\Support\Arr;
use MoeMizrak\LaravelOpenrouter\Types\RoleType;

class OpenRouterAdapter implements AIServiceInterface
{
    private $apiKey;

    /**
     * Constructor
     *
     * @param string $apiKey
     */
    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
        // Configure OpenRouter with API key
        config(['openrouter.api_key' => $apiKey]);
    }

    /**
     * Generate content using OpenRouter
     *
     * @param string $model
     * @param string|array $prompt
     * @param array $options
     * @param bool $useHtmlMeta
     * @return array
     */
    public function generate($model, $prompt, $options = [], $useHtmlMeta = false)
    {
        try {
            // Prepare messages array
            $messages = [];

            // Add system message if provided
            if (!empty($options['system_message'])) {
                $messages[] = new MessageData(
                    content: $options['system_message'],
                    role: RoleType::SYSTEM
                );
            }

            // Add user message
            $messages[] = new MessageData(
                content: is_string($prompt) ? $prompt : json_encode($prompt),
                role: RoleType::USER
            );

            // Create response format for structured output if needed
            $responseFormat = $useHtmlMeta ? new ResponseFormatData(
                type: 'json_schema',
                json_schema: [
                    'name' => 'meta_content',
                    'strict' => true,
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'meta_title' => [
                                'type' => 'string',
                                'description' => 'SEO meta title'
                            ],
                            'meta_description' => [
                                'type' => 'string',
                                'description' => 'SEO meta description'
                            ]
                        ],
                        'required' => ['meta_title', 'meta_description'],
                        'additionalProperties' => false
                    ]
                ]
            ) : null;

            // Create chat data
            $chatData = new ChatData(
                messages: $messages,
                model: $this->mapModelName($model),
                response_format: $responseFormat,
                stop: $options['stop'] ?? null,
                stream: $options['stream'] ?? false,
                max_tokens: $options['max_tokens'] ?? 1024,
                temperature: $options['temperature'] ?? 0.7,
                top_p: $options['top_p'] ?? null,
                frequency_penalty: $options['frequency_penalty'] ?? null,
                presence_penalty: $options['presence_penalty'] ?? null,
                provider: new ProviderPreferencesData(
                    require_parameters: true
                )
            );

            // Check rate limits before making request
            $limits = $this->checkLimits();
            if ($limits && $limits->credits <= 0) {
                throw new \Exception('OpenRouter credits exhausted');
            }

            // Handle streaming or regular request
            if ($options['stream'] ?? false) {
                $content = $this->handleStreamingResponse($chatData);
                $response = (object) [
                    'choices' => [
                        (object) [
                            'message' => (object) ['content' => $content]
                        ]
                    ]
                ];
            } else {
                $response = LaravelOpenRouter::chatRequest($chatData);
            }

            // Track cost if needed
            if (!empty($response->id)) {
                LaravelOpenRouter::costRequest($response->id);
            }

            // Process the response
            if ($useHtmlMeta) {
                // Extract meta information from structured response
                $content = json_decode($response->choices[0]->message->content, true);
                return [
                    'meta_title' => $content['meta_title'] ?? '',
                    'meta_description' => $content['meta_description'] ?? ''
                ];
            }

            // Return standard format
            return [
                'choices' => [
                    [
                        'message' => [
                            'content' => $response->choices[0]->message->content
                        ]
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('OpenRouter generation error', [
                'model' => $model,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Analyze an image using OpenRouter
     *
     * @param string $imageUrl
     * @param string $prompt
     * @param array $options
     * @return array
     */
    public function analyzeImage($imageUrl, $prompt, $options = [])
    {
        try {
            // Create message for image analysis
            $message = new MessageData(
                content: [
                    new TextContentData(
                        type: TextContentData::ALLOWED_TYPE,
                        text: $prompt
                    ),
                    new ImageContentPartData(
                        type: ImageContentPartData::ALLOWED_TYPE,
                        image_url: new ImageUrlData(
                            url: $imageUrl
                        )
                    )
                ],
                role: RoleType::USER
            );

            // Create chat data for vision analysis
            $chatData = new ChatData(
                messages: [$message],
                model: $this->mapModelName($options['model'] ?? 'grok-2-vision'),
                max_tokens: $options['max_tokens'] ?? 1024,
                temperature: $options['temperature'] ?? 0.7
            );

            // Check rate limits before making request
            $limits = $this->checkLimits();
            if ($limits && $limits->credits <= 0) {
                throw new \Exception('OpenRouter credits exhausted');
            }

            // Handle streaming or regular request
            if ($options['stream'] ?? false) {
                $content = $this->handleStreamingResponse($chatData);
                $response = (object) [
                    'choices' => [
                        (object) [
                            'message' => (object) ['content' => $content]
                        ]
                    ]
                ];
            } else {
                $response = LaravelOpenRouter::chatRequest($chatData);
            }

            // Track cost if needed
            if (!empty($response->id)) {
                LaravelOpenRouter::costRequest($response->id);
            }

            // Track cost if needed
            if (!empty($response->id)) {
                $cost = LaravelOpenRouter::costRequest($response->id);
                Log::info('OpenRouter image analysis cost', [
                    'generation_id' => $response->id,
                    'cost' => $cost
                ]);
            }

            // Return in standard format
            return [
                'choices' => [
                    [
                        'message' => [
                            'content' => $response->choices[0]->message->content
                        ]
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('OpenRouter vision analysis error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Get available models for OpenRouter
     *
     * @return array
     */
    public function getAvailableModels()
    {
        // Return the models from the existing mapModelName method
        return [
            'x-ai/grok-2' => 'Grok 2 (OpenRouter)',
            'x-ai/grok-2-vision-1212' => 'Grok 2 Vision (OpenRouter)',
            'deepseek/deepseek-chat:free' => 'DeepSeek Chat',
            'deepseek/deepseek-r1:free' => 'DeepSeek R1',
            'google/gemini-2.0-flash-thinking-exp:free' => 'Gemini 2.0 Flash (OR)',
            'google/gemma-3-1b-it:free' => 'Gemma 3 (OpenRouter)',
            'qwen/qwq-32b:free' => 'Qwen 32B',
            'meta-llama/llama-3.2-1b-instruct:free' => 'Llama 3.2',
            'mistralai/mistral-small-3.1-24b-instruct:free' => 'Mistral 3.1',
        ];
    }

    /**
     * Map model name to OpenRouter format
     *
     * @param string $model
     * @return string
     */
    /**
     * Check rate limits
     *
     * @return array
     */
    private function checkLimits()
    {
        try {
            return LaravelOpenRouter::limitRequest();
        } catch (\Exception $e) {
            Log::warning('Failed to check OpenRouter limits', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Handle streaming response
     *
     * @param ChatData $chatData
     * @return string
     */
    private function handleStreamingResponse(ChatData $chatData)
    {
        $content = '';
        $promise = LaravelOpenRouter::chatStreamRequest($chatData);
        $stream = $promise->wait();

        while (!$stream->eof()) {
            $rawResponse = $stream->read(1024);
            $response = LaravelOpenRouter::filterStreamingResponse($rawResponse);
            if (!empty($response)) {
                $content .= Arr::get($response[0], 'choices.0.message.content', '');
            }
        }

        return $content;
    }

    /**
     * Map model name to OpenRouter format
     *
     * @param string $model
     * @return string
     */
    private function mapModelName($model)
    {
        // Map model names from old format to OpenRouter format
        $modelMap = [
            // DeepSeek models
            'deepseek-v3' => 'deepseek/deepseek-chat:free',
            'deepseek-chat' => 'deepseek/deepseek-chat:free',
            'deepseek-r1' => 'deepseek/deepseek-r1:free',

            // Grok models - text
            'grok-2' => 'x-ai/grok-2',
            'grok-2-latest' => 'x-ai/grok-2',
            'grok-2-1212' => 'x-ai/grok-2-1212',
            'grok-2-mini' => 'x-ai/grok-2-mini',

            // Grok models - vision
            'grok-2-vision' => 'x-ai/grok-2-vision-1212',
            'grok-2-vision-latest' => 'x-ai/grok-2-vision-1212',

            // Google models
            'google/gemini-2.0-flash-thinking-exp:free' => 'google/gemini-2.0-flash-thinking-exp:free',
            'google/gemma-3-1b-it:free' => 'google/gemma-3-1b-it:free',
            'google/gemma-3-27b-it:free' => 'google/gemma-3-27b-it:free',

            // Other models
            'qwen/qwq-32b:free' => 'qwen/qwq-32b:free',
            'meta-llama/llama-3.2-1b-instruct:free' => 'meta-llama/llama-3.2-1b-instruct:free',
            'mistralai/mistral-small-3.1-24b-instruct:free' => 'mistralai/mistral-small-3.1-24b-instruct:free',
        ];

        // If the model is already in the correct format (contains a slash), use it directly
        if (str_contains($model, '/')) {
            return $model;
        }

        return $modelMap[$model] ?? 'x-ai/grok-2';  // Default to Grok-2 if unknown
    }
}
