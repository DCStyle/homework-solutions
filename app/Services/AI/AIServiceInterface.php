<?php

namespace App\Services\AI;

interface AIServiceInterface
{
    /**
     * Generate content using AI
     *
     * @param string $model
     * @param string|array $prompt
     * @param array $options
     * @param bool $useHtmlMeta
     * @return mixed
     */
    public function generate($model, $prompt, $options = [], $useHtmlMeta = false);
    
    /**
     * Analyze an image using AI
     *
     * @param string $imageUrl
     * @param string $prompt
     * @param array $options
     * @return mixed
     */
    public function analyzeImage($imageUrl, $prompt, $options = []);
    
    /**
     * Get available models for this provider
     *
     * @return array
     */
    public function getAvailableModels();
}