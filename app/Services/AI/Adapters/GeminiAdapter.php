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

    /**
     * Constructor
     *
     * @param string $apiKey
     */
    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
        // Configure Gemini with API key
        config(['gemini.api_key' => $apiKey]);
        // Initialize AIService for prompt and response processing
        $this->aiService = new AIService();
    }

    /**
     * Generate content using Gemini
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
            // Remove 'models/' prefix if present
            $modelName = ltrim(str_replace('models/', '', $model), '/');

            Log::debug('Gemini generation started', [
                'original_model' => $model,
                'normalized_model' => $modelName,
                'prompt_preview' => substr(is_string($prompt) ? $prompt : json_encode($prompt), 0, 100)
            ]);

            // Process the prompt using AIService for better structure
            // Only process string prompts, not arrays (which might be structured for vision)
            if (is_string($prompt)) {
                $contentType = $options['content_type'] ?? 'generic';
                $prompt = $this->aiService->processPrompt($prompt, $contentType, $useHtmlMeta);

                Log::debug('Prompt processed with AIService', [
                    'content_type' => $contentType,
                    'processed_prompt_preview' => substr($prompt, 0, 100) . '...'
                ]);
            }

            // Set API key for the package
            config(['gemini.api_key' => $this->apiKey]);

            // Create generation config
            $generationConfig = new GenerationConfig(
                maxOutputTokens: $options['max_tokens'] ?? 1024,
                temperature: $options['temperature'] ?? 0.7
            );

            // Add safety settings
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

            // Use generativeModel for all models - this is the recommended approach
            // for modern versions of the package
            $gemini = Gemini::generativeModel($modelName);

            // Apply configuration
            foreach ($safetySettings as $setting) {
                $gemini->withSafetySetting($setting);
            }
            $gemini->withGenerationConfig($generationConfig);

            // Generate content (streaming or regular)
            $content = '';
            try {
                if ($options['stream'] ?? false) {
                    $stream = $gemini->streamGenerateContent($prompt);
                    // Convert stream to string for our response format
                    foreach ($stream as $chunk) {
                        $content .= $chunk->text();
                    }
                } else {
                    $response = $gemini->generateContent($prompt);
                    $content = $response->text();
                }
            } catch (\Exception $e) {
                Log::error('Gemini API error details', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'model_requested' => $model
                ]);
                throw new \Exception('Gemini API error: ' . $e->getMessage());
            }

            if (!$content) {
                throw new \Exception('Empty response from Gemini API');
            }

            // Format the response structure for our application
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
            Log::error('Gemini generation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Analyze an image using Gemini Vision
     *
     * @param string $imageUrl
     * @param string $prompt
     * @param array $options
     * @return array
     */
    public function analyzeImage($imageUrl, $prompt, $options = [])
    {
        if (empty($imageUrl)) {
            throw new \Exception('Image URL is required');
        }

        try {
            // Set API key for the package
            config(['gemini.api_key' => $this->apiKey]);

            // Get image data
            $imageData = file_get_contents($imageUrl);
            if (!$imageData) {
                throw new \Exception('Failed to load image from URL');
            }

            // Create image blob
            $imageBlob = new Blob(
                mimeType: MimeType::IMAGE_JPEG, // Adjust based on image type if needed
                data: base64_encode($imageData)
            );

            // Determine which vision model to use
            $visionModel = $options['model'] ?? 'gemini-pro-vision';

            // Normalize model name
            $visionModel = ltrim(str_replace('models/', '', $visionModel), '/');

            // Generate content with image
            $response = Gemini::generativeModel($visionModel)
                ->generateContent([
                    $prompt,
                    $imageBlob
                ]);

            $content = $response->text();

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
            $contentType = $options['content_type'] ?? 'generic';
            return $this->aiService->processResponse($formattedResponse, $contentType, $options);
        } catch (\Exception $e) {
            Log::error('Gemini vision analysis error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Get available models for Gemini
     *
     * @return array
     */
    public function getAvailableModels()
    {
        // If we've already fetched the models, return the cached result
        if ($this->availableModels !== null) {
            return $this->availableModels;
        }

        // Try to get the list of models from the API
        try {
            $response = Gemini::models()->list();
            $models = [];

            if (isset($response->models) && is_array($response->models)) {
                foreach ($response->models as $model) {
                    $name = $model->name;

                    // Remove 'models/' prefix for consistency
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

        // Fallback to hardcoded list if API call fails
        return [
            // All models use generativeModel() approach
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

    /**
     * Start a chat session
     *
     * @param array $history Previous chat history
     * @return mixed
     */
    public function startChat($history = [])
    {
        try {
            config(['gemini.api_key' => $this->apiKey]);
            return Gemini::chat()->startChat($history);
        } catch (\Exception $e) {
            Log::error('Error starting chat session', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
