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

    /**
     * Constructor
     *
     * @param string $apiKey
     */
    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
        // Configure Grok with API key
        config(['grok.api_key' => $apiKey]);
        // Initialize AIService for prompt and response processing
        $this->aiService = new AIService();
    }

    /**
     * Generate content using Grok AI
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
            // Log the original model and prompt
            Log::debug('Grok generation started', [
                'model' => $model,
                'prompt_preview' => substr(is_string($prompt) ? $prompt : json_encode($prompt), 0, 100)
            ]);

            // Process the prompt using AIService for better structure
            // Only process string prompts, not arrays
            if (is_string($prompt)) {
                $contentType = $options['content_type'] ?? 'generic';
                $processedPrompt = $this->aiService->processPrompt($prompt, $contentType, $useHtmlMeta);

                Log::debug('Prompt processed with AIService', [
                    'content_type' => $contentType,
                    'processed_prompt_preview' => substr($processedPrompt, 0, 100) . '...'
                ]);
            } else {
                $processedPrompt = $prompt;
            }

            // Format the prompt as messages for chat API
            $messages = $this->formatPromptAsMessages($processedPrompt);

            // Create chat options
            $chatOptions = new ChatOptions(
                model: $this->getGrokModel($model),
                temperature: $options['temperature'] ?? 0.7,
                stream: $options['stream'] ?? false
            );

            // Generate response using Grok
            $content = '';
            try {
                // Handle streaming responses
                if ($options['stream'] ?? false) {
                    $response = GrokAI::chat($messages, $chatOptions);
                    foreach ($response as $chunk) {
                        $content .= $chunk->content();
                    }
                } else {
                    $response = GrokAI::chat($messages, $chatOptions);
                    $content = $response->content();
                }
            } catch (GrokException $e) {
                Log::error('Grok API error details', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'model_requested' => $model
                ]);
                throw new \Exception('Grok API error: ' . $e->getMessage());
            }

            if (empty($content)) {
                throw new \Exception('Empty response from Grok API');
            }

            // Format response to match our application's expected format
            $formattedResponse = [
                'choices' => [
                    [
                        'message' => [
                            'content' => $content
                        ]
                    ]
                ]
            ];

            // Process the response using AIService for cleaning and formatting
            $contentType = $options['content_type'] ?? 'generic';
            $processedResponse = $this->aiService->processResponse($formattedResponse, $contentType, $options);

            Log::debug('Response processed with AIService', [
                'content_type' => $contentType,
                'response_type' => gettype($processedResponse),
                'is_array' => is_array($processedResponse) ? 'true' : 'false'
            ]);

            return $processedResponse;
        } catch (\Exception $e) {
            Log::error('Grok generation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Analyze an image using Grok Vision
     *
     * @param string $imageUrl
     * @param string $prompt
     * @param array $options
     * @return array
     */
    public function analyzeImage($imageUrl, $prompt, $options = [])
    {
        try {
            // Process the vision prompt with AIService if needed
            $contentType = $options['content_type'] ?? 'image_analysis';
            $processedPrompt = $this->aiService->processPrompt($prompt, $contentType, false);

            // Use Grok Vision API
            $response = GrokAI::vision()->analyze(
                $imageUrl,
                $processedPrompt,
                $this->getGrokModel($options['model'] ?? 'grok-2-vision')
            );

            $content = $response->content();

            // Format to match our application expected format
            $formattedResponse = [
                'choices' => [
                    [
                        'message' => [
                            'content' => $content
                        ]
                    ]
                ]
            ];

            // Process the response using AIService for cleaning and formatting
            return $this->aiService->processResponse($formattedResponse, $contentType, $options);
        } catch (\Exception $e) {
            Log::error('Grok vision analysis error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Get available models for Grok
     *
     * @return array
     */
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

    /**
     * Map model name to Grok model enum
     *
     * @param string $model
     * @return Model
     */
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

    /**
     * Format a prompt into messages for Grok
     *
     * @param string|array $prompt
     * @return array
     */
    private function formatPromptAsMessages($prompt)
    {
        // If prompt is already an array of messages, return as is
        if (is_array($prompt) && isset($prompt[0]['role'])) {
            return $prompt;
        }

        // Convert string prompt to a single user message
        return [
            ['role' => 'user', 'content' => is_string($prompt) ? $prompt : json_encode($prompt)]
        ];
    }
}
