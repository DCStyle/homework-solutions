<?php

namespace App\Services\AI;

use App\Models\AIApiKey;
use App\Services\AI\Adapters\GeminiAdapter;
use App\Services\AI\Adapters\GrokAdapter;
use App\Services\AI\Adapters\OpenRouterAdapter;

class AIServiceFactory
{
    /**
     * Create an AI service for the specified provider
     *
     * @param string|null $provider
     * @return AIServiceInterface
     * @throws \Exception
     */
    public static function createService($provider = null)
    {
        if (!$provider) {
            // Default provider if none specified
            $provider = 'openrouter';
        }

        // Get a random API key for the provider
        $apiKeyModel = AIApiKey::getRandomKeyForProvider($provider);

        if (!$apiKeyModel) {
            throw new \Exception("No active API key found for provider: {$provider}");
        }

        $apiKey = $apiKeyModel->api_key;

        switch ($provider) {
            case 'google-gemini':
                return new GeminiAdapter($apiKey);
            case 'xai-grok':
                return new GrokAdapter($apiKey);
            case 'openrouter':
                return new OpenRouterAdapter($apiKey);
            default:
                throw new \Exception("Unsupported AI provider: {$provider}");
        }
    }

    /**
     * Get all available providers with their display names
     *
     * @return array
     */
    public static function getAvailableProviders()
    {
        return [
            'google-gemini' => 'Google Gemini',
            'xai-grok' => 'xAI Grok',
            'openrouter' => 'OpenRouter'
        ];
    }

    /**
     * Get active providers that have API keys
     *
     * @return array
     */
    public static function getActiveProviders()
    {
        $activeProviderCodes = AIApiKey::getActiveProviders();
        $allProviders = self::getAvailableProviders();

        $activeProviders = [];
        foreach ($activeProviderCodes as $code) {
            if (isset($allProviders[$code])) {
                $activeProviders[$code] = $allProviders[$code];
            }
        }

        return $activeProviders;
    }
}
