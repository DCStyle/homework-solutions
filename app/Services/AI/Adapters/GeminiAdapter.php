<?php

namespace App\Services\AI\Adapters;

use App\Services\AI\AIServiceInterface;
use App\Services\AIService;
use Illuminate\Support\Facades\Log;
use Gemini\Laravel\Facades\Gemini;
use Gemini\Data\GenerationConfig;
use Gemini\Data\SafetySetting;
use Gemini\Enums\HarmCategory;
use Gemini\Enums\HarmBlockThreshold;
use Gemini\Data\Blob;
use Gemini\Enums\MimeType;

class GeminiAdapter implements AIServiceInterface
{
    private $apiKey;
    private $availableModels = null;
    private $aiService;

    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
        config(['gemini.api_key' => $apiKey]);
        $this->aiService = new AIService();
    }

    public function generate($model, $prompt, $options = [], $useHtmlMeta = false)
    {
        try {
            $modelName = ltrim(str_replace('models/', '', $model), '/');

            Log::debug('Gemini generation started', [
                'original_model' => $model,
                'normalized_model' => $modelName,
                'prompt_preview' => substr(is_string($prompt) ? $prompt : json_encode($prompt), 0, 100)
            ]);

            if (is_string($prompt)) {
                $contentType = $options['content_type'] ?? 'generic';
                $prompt = $this->aiService->processPrompt($prompt, $contentType, $useHtmlMeta);
            }

            config(['gemini.api_key' => $this->apiKey]);

            $generationConfig = new GenerationConfig(
                maxOutputTokens: $options['max_tokens'] ?? 1024,
                temperature: $options['temperature'] ?? 0.7
            );

            $safetySettings = [
                new SafetySetting(
                    category: HarmCategory::HARM_CATEGORY_DANGEROUS_CONTENT,
                    threshold: HarmBlockThreshold::BLOCK_MEDIUM_AND_ABOVE
                ),
                new SafetySetting(
                    category: HarmCategory::HARM_CATEGORY_HATE_SPEECH,
                    threshold: HarmBlockThreshold::BLOCK_MEDIUM_AND_ABOVE
                )
            ];

            $gemini = Gemini::generativeModel($modelName);
            foreach ($safetySettings as $setting) {
                $gemini->withSafetySetting($setting);
            }
            $gemini->withGenerationConfig($generationConfig);

            // Handle streaming response
            if ($options['stream'] ?? false) {
                return $this->handleStreamingResponse($gemini, $prompt, $options);
            }

            // Handle regular response
            $response = $gemini->generateContent($prompt);
            $content = $response->text();

            if (empty($content)) {
                throw new \Exception('Empty response from Gemini API');
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

            return $this->aiService->processResponse($formattedResponse, $options['content_type'] ?? 'generic', $options);
        } catch (\Exception $e) {
            Log::error('Gemini generation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function handleStreamingResponse($gemini, $prompt, $options)
    {
        $stream = $gemini->streamGenerateContent($prompt);
        $content = '';
        $streamCallback = $options['stream_callback'] ?? null;

        try {
            foreach ($stream as $chunk) {
                $chunkContent = $chunk->text();
                
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
        } catch (\Exception $e) {
            Log::error('Gemini streaming error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function analyzeImage($imageUrl, $prompt, $options = [])
    {
        if (empty($imageUrl)) {
            throw new \Exception('Image URL is required');
        }

        try {
            config(['gemini.api_key' => $this->apiKey]);

            $imageData = file_get_contents($imageUrl);
            if (!$imageData) {
                throw new \Exception('Failed to load image from URL');
            }

            $imageBlob = new Blob(
                mimeType: MimeType::IMAGE_JPEG,
                data: base64_encode($imageData)
            );

            $visionModel = $options['model'] ?? 'gemini-pro-vision';
            $visionModel = ltrim(str_replace('models/', '', $visionModel), '/');

            $response = Gemini::generativeModel($visionModel)
                ->generateContent([
                    $prompt,
                    $imageBlob
                ]);

            $content = $response->text();

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
        } catch (\Exception $e) {
            Log::error('Gemini vision analysis error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function getAvailableModels()
    {
        if ($this->availableModels !== null) {
            return $this->availableModels;
        }

        try {
            $response = Gemini::models()->list();
            $models = [];

            if (isset($response->models) && is_array($response->models)) {
                foreach ($response->models as $model) {
                    $name = $model->name;
                    $key = ltrim(str_replace('models/', '', $name), '/');
                    $models[$key] = $model->displayName . ' - ' . $model->description;
                }

                $this->availableModels = $models;
                return $models;
            }
        } catch (\Exception $e) {
            Log::warning('Failed to fetch Gemini models from API', [
                'error' => $e->getMessage()
            ]);
        }

        return [
            'gemini-1.0-pro' => 'Gemini 1.0 Pro',
            'gemini-pro-vision' => 'Gemini Pro Vision',
            'gemini-1.5-pro' => 'Gemini 1.5 Pro',
            'gemini-1.5-flash' => 'Gemini 1.5 Flash',
            'gemini-1.5-flash-latest' => 'Gemini 1.5 Flash (Latest)',
            'gemini-2.0-pro' => 'Gemini 2.0 Pro',
            'gemini-2.0-flash' => 'Gemini 2.0 Flash',
            'embedding-001' => 'Embedding 001'
        ];
    }
}
