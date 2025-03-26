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
use Illuminate\Support\Arr;
use MoeMizrak\LaravelOpenrouter\Facades\LaravelOpenRouter;
use MoeMizrak\LaravelOpenrouter\Types\RoleType;

class OpenRouterAdapter implements AIServiceInterface
{
    private $apiKey;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
        config(['openrouter.api_key' => $apiKey]);
    }

    public function generate($model, $prompt, $options = [], $useHtmlMeta = false)
    {
        try {
            $messages = [];

            if (!empty($options['system_message'])) {
                $messages[] = new MessageData(
                    content: $options['system_message'],
                    role: RoleType::SYSTEM
                );
            }

            $messages[] = new MessageData(
                content: is_string($prompt) ? $prompt : json_encode($prompt),
                role: RoleType::USER
            );

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

            $limits = $this->checkLimits();
            if ($limits && $limits->credits <= 0) {
                throw new \Exception('OpenRouter credits exhausted');
            }

            // Handle streaming
            if ($options['stream'] ?? false) {
                return $this->handleStreamingResponse($chatData, $options);
            }

            // Handle regular response
            $response = LaravelOpenRouter::chatRequest($chatData);

            // Track cost if needed
            if (!empty($response->id)) {
                LaravelOpenRouter::costRequest($response->id);
            }

            if ($useHtmlMeta) {
                $content = json_decode($response->choices[0]->message->content, true);
                return [
                    'meta_title' => $content['meta_title'] ?? '',
                    'meta_description' => $content['meta_description'] ?? ''
                ];
            }

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

    private function handleStreamingResponse(ChatData $chatData, array $options)
    {
        try {
            $content = '';
            $streamCallback = $options['stream_callback'] ?? null;
            $promise = LaravelOpenRouter::chatStreamRequest($chatData);
            $stream = $promise->wait();

            while (!$stream->eof()) {
                $rawResponse = $stream->read(1024);
                $response = LaravelOpenRouter::filterStreamingResponse($rawResponse);

                if (!empty($response)) {
                    $chunkContent = Arr::get($response[0], 'choices.0.message.content', '');

                    // Handle stream callback if provided
                    if ($streamCallback && is_callable($streamCallback)) {
                        $streamCallback($chunkContent);
                    } elseif ($options['flush_output'] ?? false) {
                        // Direct output for HTTP streaming
                        echo $chunkContent;
                        ob_flush();
                        flush();
                    }

                    $content .= $chunkContent;
                }
            }

            return [
                'choices' => [
                    [
                        'message' => [
                            'content' => $content
                        ]
                    ]
                ],
                'usage' => [
                    'prompt_tokens' => -1,
                    'completion_tokens' => -1,
                    'total_tokens' => -1
                ]
            ];
        } catch (\Exception $e) {
            Log::error('OpenRouter streaming error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function analyzeImage($imageUrl, $prompt, $options = [])
    {
        try {
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

            $chatData = new ChatData(
                messages: [$message],
                model: $this->mapModelName($options['model'] ?? 'grok-2-vision'),
                max_tokens: $options['max_tokens'] ?? 1024,
                temperature: $options['temperature'] ?? 0.7
            );

            $limits = $this->checkLimits();
            if ($limits && $limits->credits <= 0) {
                throw new \Exception('OpenRouter credits exhausted');
            }

            if ($options['stream'] ?? false) {
                return $this->handleStreamingResponse($chatData, $options);
            }

            $response = LaravelOpenRouter::chatRequest($chatData);

            if (!empty($response->id)) {
                LaravelOpenRouter::costRequest($response->id);
            }

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

    public function getAvailableModels()
    {
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

    private function mapModelName($model)
    {
        $modelMap = [
            'deepseek-v3' => 'deepseek/deepseek-chat:free',
            'deepseek-chat' => 'deepseek/deepseek-chat:free',
            'deepseek-r1' => 'deepseek/deepseek-r1:free',
            'grok-2' => 'x-ai/grok-2',
            'grok-2-latest' => 'x-ai/grok-2',
            'grok-2-1212' => 'x-ai/grok-2-1212',
            'grok-2-mini' => 'x-ai/grok-2-mini',
            'grok-2-vision' => 'x-ai/grok-2-vision-1212',
            'grok-2-vision-latest' => 'x-ai/grok-2-vision-1212',
            'google/gemini-2.0-flash-thinking-exp:free' => 'google/gemini-2.0-flash-thinking-exp:free',
            'google/gemma-3-1b-it:free' => 'google/gemma-3-1b-it:free',
            'google/gemma-3-27b-it:free' => 'google/gemma-3-27b-it:free',
            'qwen/qwq-32b:free' => 'qwen/qwq-32b:free',
            'meta-llama/llama-3.2-1b-instruct:free' => 'meta-llama/llama-3.2-1b-instruct:free',
            'mistralai/mistral-small-3.1-24b-instruct:free' => 'mistralai/mistral-small-3.1-24b-instruct:free',
        ];

        if (str_contains($model, '/')) {
            return $model;
        }

        return $modelMap[$model] ?? 'x-ai/grok-2';
    }
}
