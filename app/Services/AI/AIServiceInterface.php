<?php

namespace App\Services\AI;

interface AIServiceInterface
{
    /**
     * Generate content using AI
     *
     * @param string $model
     * @param string|array $prompt
     * @param array $options {
     *      @var bool $stream Whether to stream the response
     *      @var callable|null $stream_callback Callback function for streaming responses
     *      @var bool $flush_output Whether to flush output buffer for HTTP streaming
     *      @var int $max_tokens Maximum tokens to generate
     *      @var float $temperature Temperature for response generation
     *      @var float $top_p Top P sampling value
     *      @var string $content_type Type of content being generated
     *      @var array $stop Sequence where the API will stop generating
     * }
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