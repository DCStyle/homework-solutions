<?php

namespace App\Services\AI\Adapters;

use App\Services\AI\AIServiceInterface;
use App\Services\AIService;
use Illuminate\Support\Facades\Log;
use GrokPHP\Laravel\Facades\GrokAI;
use GrokPHP\Client\Config\ChatOptions;
use GrokPHP\Client\Enums\Model;
use GrokPHP\Client\Exceptions\GrokException;

class GrokAdapter implements AIServiceInterface
{
    private $apiKey;
    private $aiService;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
        config(['grok.api_key' => $apiKey]);
        $this->aiService = new AIService();
    }

    public function generate($model, $prompt, $options = [], $useHtmlMeta = false)
    {
        try {
            Log::debug('Grok generation started', [
                'model' => $model,
                'prompt_preview' => substr(is_string($prompt) ? $prompt : json_encode($prompt), 0, 100)
            ]);

            if (is_string($prompt)) {
                $contentType = $options['content_type'] ?? 'generic';
                $processedPrompt = $this->aiService->processPrompt($prompt, $contentType, $useHtmlMeta);
            } else {
                $processedPrompt = $prompt;
            }

            $messages = $this->formatPromptAsMessages($processedPrompt);

            // Create chat options
            $chatOptions = new ChatOptions(
                model: $this->getGrokModel($model),
                temperature: $options['temperature'] ?? 0.7,
                stream: $options['stream'] ?? false,
                maxTokens: $options['max_tokens'] ?? 1024,
                topP: $options['top_p'] ?? 1
            );

            // Handle streaming response
            if ($options['stream'] ?? false) {
                return $this->handleStreamingResponse($messages, $chatOptions, $options);
            }

            // Handle regular response
            $response = GrokAI::chat($messages, $chatOptions);
            $content = is_object($response) && method_exists($response, 'content') 
                ? $response->content() 
                : (is_array($response) && isset($response['content']) 
                    ? $response['content'] 
                    : (string)$response);

            if (empty($content)) {
                throw new \Exception('Empty response from Grok API');
            }

            $formattedResponse = [
                'choices' => [
                    [
                        'message' => [
                            'content' => $content
                        ]
                    ]
                ]
            ];

            return $this->aiService->processResponse(
                $formattedResponse,
                $options['content_type'] ?? 'generic',
                $options
            );

        } catch (GrokException $e) {
            Log::error('Grok API error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Grok API error: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Grok generation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function handleStreamingResponse($messages, ChatOptions $chatOptions, array $options)
    {
        try {
            $response = GrokAI::chat($messages, $chatOptions);
            $content = '';
            $streamCallback = $options['stream_callback'] ?? null;

            foreach ($response as $chunk) {
                $chunkContent = $chunk->content() ?? '';
                
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

        } catch (GrokException $e) {
            Log::error('Grok streaming error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Grok streaming error: ' . $e->getMessage());
        }
    }

    public function analyzeImage($imageUrl, $prompt, $options = [])
    {
        try {
            $contentType = $options['content_type'] ?? 'image_analysis';
            $processedPrompt = $this->aiService->processPrompt($prompt, $contentType, false);

            $response = GrokAI::vision()->analyze(
                $imageUrl,
                $processedPrompt,
                $this->getGrokModel($options['model'] ?? 'grok-2-vision')
            );

            $content = is_object($response) && method_exists($response, 'content')
                ? $response->content()
                : (is_array($response) && isset($response['content'])
                    ? $response['content']
                    : (string)$response);

            $formattedResponse = [
                'choices' => [
                    [
                        'message' => [
                            'content' => $content
                        ]
                    ]
                ]
            ];

            return $this->aiService->processResponse(
                $formattedResponse,
                $contentType,
                $options
            );
        } catch (\Exception $e) {
            Log::error('Grok vision analysis error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function getAvailableModels()
    {
        return [
            'grok-2' => 'Grok 2 (Default)',
            'grok-2-latest' => 'Grok 2 Latest',
            'grok-2-vision' => 'Grok 2 Vision',
            'grok-2-vision-latest' => 'Grok 2 Vision Latest',
            'grok-2-1212' => 'Grok 2 1212',
            'grok-beta' => 'Grok Beta',
            'grok-vision-beta' => 'Grok Vision Beta',
        ];
    }

    private function getGrokModel($model)
    {
        return match($model) {
            'grok-2' => Model::GROK_2,
            'grok-2-latest' => Model::GROK_2_LATEST,
            'grok-2-vision' => Model::GROK_2_VISION,
            'grok-2-vision-latest' => Model::GROK_2_VISION_LATEST,
            'grok-2-1212' => Model::GROK_2_1212,
            'grok-beta' => Model::GROK_BETA,
            'grok-vision-beta' => Model::GROK_VISION_BETA,
            default => Model::GROK_2
        };
    }

    private function formatPromptAsMessages($prompt)
    {
        if (is_array($prompt) && isset($prompt[0]['role'])) {
            return $prompt;
        }

        return [
            ['role' => 'user', 'content' => is_string($prompt) ? $prompt : json_encode($prompt)]
        ];
    }
}
