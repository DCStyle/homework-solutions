<?php

namespace App\Services;

use MoeMizrak\LaravelOpenrouter\DTO\ChatData;
use MoeMizrak\LaravelOpenrouter\DTO\ImageContentPartData;
use MoeMizrak\LaravelOpenrouter\DTO\ImageUrlData;
use MoeMizrak\LaravelOpenrouter\DTO\MessageData;
use MoeMizrak\LaravelOpenrouter\DTO\ProviderPreferencesData;
use MoeMizrak\LaravelOpenrouter\DTO\ResponseFormatData;
use MoeMizrak\LaravelOpenrouter\DTO\TextContentData;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use MoeMizrak\LaravelOpenrouter\Facades\LaravelOpenRouter;
use MoeMizrak\LaravelOpenrouter\Types\RoleType;

class AIService
{
    /**
     * Call AI model to generate content
     *
     * @param string $model The model to use
     * @param string $prompt The prompt to send to the model
     * @param array $options Additional options for the API call
     *
     * @return string|array The generated content
     */
    public function generate($model, $prompt, $options = [], $useHtmlMeta = false)
    {
        try {
            // Map model names to OpenRouter format
            $openRouterModel = $this->mapModelName($model);
            
            // Process the prompt to ensure it's in the right format
            $processedPrompt = $this->processPrompt($prompt, $options['content_type'] ?? 'default', $useHtmlMeta);
            
            // Set up message for chat models
            $userMessage = new MessageData(
                role: RoleType::USER,
                content: [
                    new TextContentData(
                        type: TextContentData::ALLOWED_TYPE,
                        text: $processedPrompt
                    )
                ]
            );
            
            // Configure response format - we want JSON for structured data
            $responseFormatData = [
                'type' => 'json_object'
            ];
            
            // Create the chat data from an array to avoid property access issues
            $chatData = [
                'model' => $openRouterModel,
                'messages' => [$userMessage],
                'temperature' => isset($options['temperature']) ? (float) $options['temperature'] : 0.7,
                'top_p' => 1,
                'max_tokens' => isset($options['max_tokens']) ? (int) $options['max_tokens'] : 1000,
                'response_format' => $responseFormatData
            ];
            
            // Add system message if provided (for models that support it like DeepSeek)
            if (isset($options['system_message']) && !empty($options['system_message'])) {
                $systemMessage = new MessageData(
                    role: RoleType::SYSTEM,
                    content: [
                        new TextContentData(
                            type: TextContentData::ALLOWED_TYPE,
                            text: $options['system_message']
                        )
                    ]
                );
                
                $chatData['messages'] = [$systemMessage, $userMessage];
            }
            
            // Add provider preferences if needed
            if (isset($options['model_variant']) && $options['model_variant'] === 'deepseek-chat') {
                $chatData['provider_preferences'] = [
                    [
                        'model' => $openRouterModel,
                        'provider' => 'deepseek'
                    ]
                ];
            }
            
            // Create the chat object using fromArray to ensure proper validation
            $chat = ChatData::from($chatData);
            
            try {
                // Add retry logic for API calls
                $maxRetries = 3;
                $retryCount = 0;
                $lastException = null;
                
                while ($retryCount < $maxRetries) {
                    try {
                        // Make the API call
                        $response = LaravelOpenRouter::chatRequest($chat);
                        
                        // Check if response contains valid choices and content
                        if (!$this->isValidResponse($response)) {
                            Log::warning('Invalid response structure from OpenRouter', [
                                'model' => $model,
                                'content_type' => $options['content_type'] ?? 'default',
                                'response_preview' => $this->getResponsePreview($response)
                            ]);
                            
                            // If this is the last retry, use fallback
                            if ($retryCount == $maxRetries - 1) {
                                return $this->getFallbackResponse($options['content_type'] ?? 'default');
                            }
                            
                            // Otherwise retry
                            $retryCount++;
                            sleep(1); // Wait before retry
                            continue;
                        }
                        
                        // Process the response
                        return $this->processResponse($response, $options['content_type'] ?? 'default', $options);
                    } catch (\TypeError $e) {
                        $lastException = $e;
                        
                        // Handle case where OpenRouter returns a null ID in the response
                        if (strpos($e->getMessage(), 'Argument #1 ($id) must be of type string, null given') !== false) {
                            Log::warning('OpenRouter returned null ID in response, retrying', [
                                'model' => $model,
                                'content_type' => $options['content_type'] ?? 'default',
                                'retry_count' => $retryCount + 1,
                                'max_retries' => $maxRetries
                            ]);
                            
                            // If this is the last retry, use fallback
                            if ($retryCount == $maxRetries - 1) {
                                return $this->getFallbackResponse($options['content_type'] ?? 'default');
                            }
                            
                            // Otherwise retry
                            $retryCount++;
                            sleep(1); // Wait before retry
                            continue;
                        }
                        
                        // Re-throw other type errors
                        throw $e;
                    } catch (\Exception $e) {
                        $lastException = $e;
                        
                        // Log the error
                        Log::warning('Error in API call, retrying', [
                            'error' => $e->getMessage(),
                            'model' => $model,
                            'retry_count' => $retryCount + 1,
                            'max_retries' => $maxRetries
                        ]);
                        
                        // If this is the last retry, use fallback
                        if ($retryCount == $maxRetries - 1) {
                            Log::error('All retry attempts failed for OpenRouter API call', [
                                'error' => $e->getMessage(),
                                'model' => $model,
                                'content_type' => $options['content_type'] ?? 'default'
                            ]);
                            return $this->getFallbackResponse($options['content_type'] ?? 'default');
                        }
                        
                        // Otherwise retry
                        $retryCount++;
                        sleep(1); // Wait before retry
                        continue;
                    }
                }
                
                // If we get here, all retries failed
                Log::error('All retry attempts failed for OpenRouter API call', [
                    'model' => $model,
                    'content_type' => $options['content_type'] ?? 'default',
                    'last_error' => $lastException ? $lastException->getMessage() : 'Unknown error'
                ]);
                return $this->getFallbackResponse($options['content_type'] ?? 'default');
                
            } catch (\TypeError $e) {
                // Handle case where OpenRouter returns a null ID in the response
                if (strpos($e->getMessage(), 'Argument #1 ($id) must be of type string, null given') !== false) {
                    Log::warning('OpenRouter returned null ID in response, using fallback response', [
                        'model' => $model,
                        'content_type' => $options['content_type'] ?? 'default'
                    ]);
                    
                    // Return a fallback response based on content type
                    return $this->getFallbackResponse($options['content_type'] ?? 'default');
                }
                
                // Re-throw other type errors
                throw $e;
            }
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error generating content with AI', [
                'error' => $e->getMessage(),
                'model' => $model,
                'content_type' => $options['content_type'] ?? 'default'
            ]);

            return $this->getFallbackResponse($options['content_type'] ?? 'default');
        }
    }
    
    /**
     * Check if the OpenRouter response is valid
     */
    private function isValidResponse($response) 
    {
        if (!isset($response->choices) || empty($response->choices)) {
            return false;
        }
        
        $choice = $response->choices[0] ?? null;
        if (!$choice) {
            return false;
        }
        
        if (!isset($choice->message) || !isset($choice->message->content)) {
            return false;
        }
        
        $content = $choice->message->content;
        if (empty($content) || $content === '{}' || $content === '[]' || $content === '[1]<|eos|>') {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get a preview of the response for logging purposes
     */
    private function getResponsePreview($response) 
    {
        try {
            $responseArr = (array)$response;
            // Remove large objects to prevent log bloat
            unset($responseArr['usage']);
            
            // If choices exist, get first choice content preview
            if (isset($responseArr['choices']) && !empty($responseArr['choices'])) {
                $choice = $responseArr['choices'][0];
                if (isset($choice->message) && isset($choice->message->content)) {
                    $content = $choice->message->content;
                    $responseArr['content_preview'] = substr($content, 0, 50) . (strlen($content) > 50 ? '...' : '');
                }
            }
            
            return json_encode($responseArr);
        } catch (\Exception $e) {
            return 'Could not generate response preview: ' . $e->getMessage();
        }
    }

    private function processPrompt($prompt, $contentType, $useHtmlMeta = false)
    {
        // Base system messages with common instructions
        $baseMessages = [
            'posts' => "You are an SEO specialist for educational content. Format your response EXACTLY as follows:
Meta Title: [Your title here - DO NOT INCLUDE HTML TAGS HERE]
Meta Description: [Your description here]

Do not include any other text, explanations, or formatting. Just provide the Meta Title and Meta Description with these exact labels.",

            'chapters' => "You are an educational content writer.",
            'books' => "You are an educational content specialist.",
            'book_groups' => "You are a curriculum specialist.",
            'generic' => "Format your response clearly and consistently. If providing metadata, use explicit labels like 'Meta Title:' and 'Meta Description:'."
        ];

        $outputSystemMessage = $baseMessages[$contentType] ?? $baseMessages['generic'];

        $prompt .= $outputSystemMessage . "\n\n WRITE EVERYTHING IN VIETNAMESE, IN EXACT OUTPUT LENGTH AS REQUESTED. DO NOT CUT THE LENGTH
OR SHORTEN THE OUTPUT RESPONSE, OR DO ANYTHING OTHER THAN REQUESTED!!! REMEMBER TO DO EXACTLY WHATEVER I REQUEST, NO MATER WHAT'S YOUR LIMIT WHATSOEVER.\n\n";

        // Add HTML formatting instructions for posts when requested
        if ($useHtmlMeta) {
            $prompt .= "Remember to use basic HTML formatting:
- Use <p> tags for paragraphs
- Use <strong> for important terms or emphasis
- Use <em> for light emphasis
- Use <ul> and <li> for lists
- Keep the HTML clean and simple

Do not include any other text, explanations, or formatting.";
        }

        return $prompt;
    }

    /**
     * Map old model names to OpenRouter model IDs
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
        ];

        // If the model is already in the correct format (contains a slash), use it directly
        if (str_contains($model, '/')) {
            return $model;
        }

        return $modelMap[$model] ?? 'x-ai/grok-2';  // Default to Grok-2 if unknown
    }

    /**
     * Process and format response based on content type
     */
    private function processResponse($response, $contentType, $options = [])
    {
        try {
            // Extract response content from OpenRouter response structure
            $content = '';

            // Check if response is in the expected format
            if (isset($response->choices) && !empty($response->choices)) {
                if (isset($response->choices[0]->message) && isset($response->choices[0]->message->content)) {
                    $content = $response->choices[0]->message->content;
                } else {
                    Log::warning('Unexpected response structure', [
                        'response' => json_encode(Arr::except((array)$response, ['usage']))
                    ]);
                    $content = json_encode($response->choices[0]);
                }
            } else {
                Log::warning('Choices not found in response', [
                    'response_keys' => array_keys((array)$response)
                ]);
                // Try to extract content from the first level if choices is missing
                $content = $response->content ?? json_encode($response);
            }

            switch ($contentType) {
                case 'posts':
                    // For posts, extract meta title and description
                    Log::debug('Processing posts response', ['content_preview' => substr($content, 0, 100)]);

                    // Try to parse JSON if it's a JSON response
                    if ($this->isJson($content)) {
                        $jsonContent = json_decode($content, true);
                        return [
                            'meta_title' => $jsonContent['Meta Title'] ?? $jsonContent['meta_title'] ?? $this->extractMetaTitle($content),
                            'meta_description' => $jsonContent['Meta Description'] ?? $jsonContent['meta_description'] ?? $this->extractMetaDescription($content, $options['use_html_meta'] ?? false)
                        ];
                    }

                    // Otherwise use regex extraction
                    return [
                        'meta_title' => $this->extractMetaTitle($content),
                        'meta_description' => $this->extractMetaDescription($content, $options['use_html_meta'] ?? false)
                    ];

                case 'chapters':
                case 'books':
                case 'book_groups':
                    // For these types, the entire response is the description
                    Log::debug('Processing description response', ['content_preview' => substr($content, 0, 100)]);
                    return $this->cleanupDescription($content);

                default:
                    return $content;
            }
        } catch (\Exception $e) {
            Log::error('Error processing response', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $content ?? 'Error processing response';
        }
    }

    /**
     * Check if a string is valid JSON
     */
    private function isJson($string) {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Extract meta title from response
     */
    private function extractMetaTitle($content)
    {
        // First try to extract using markdown or specific formatting
        if (preg_match('/(?:meta title|tiêu đề meta):?\s*([^\n]+)/i', $content, $matches)) {
            return trim($matches[1]);
        }

        if (preg_match('/(?:title|tiêu đề):?\s*([^\n]+)/i', $content, $matches)) {
            return trim($matches[1]);
        }

        // Try to extract from structured patterns
        if (preg_match('/1\.\s*([^\n]+)/i', $content, $matches)) {
            return trim($matches[1]);
        }

        // Fallback: take first line or first 60 chars
        $lines = preg_split('/\r\n|\r|\n/', $content);
        $firstLine = trim($lines[0] ?? '');

        if (strlen($firstLine) > 0 && strlen($firstLine) <= 70) {
            return $firstLine;
        }

        return substr($content, 0, 60);
    }

    /**
     * Extract meta description from response
     */
    private function extractMetaDescription($content, $useHtmlMeta = false)
    {
        // First try to extract using markdown or specific formatting
        if (preg_match('/(?:meta description|mô tả meta):?\s*([^\n]+(?:\n[^#\n][^\n]*)*)/i', $content, $matches)) {
            $description = trim($matches[1]);
        } elseif (preg_match('/(?:description|mô tả):?\s*([^\n]+(?:\n[^#\n][^\n]*)*)/i', $content, $matches)) {
            $description = trim($matches[1]);
        } elseif (preg_match('/2\.\s*([^\n]+(?:\n[^#\n][^\n]*)*)/i', $content, $matches)) {
            // Try to extract from structured patterns
            $description = trim($matches[1]);
        } else {
            // Fallback: take second paragraph or portion of content
            $paragraphs = preg_split('/\r\n\r\n|\r\r|\n\n/', $content);

            if (isset($paragraphs[1])) {
                $description = trim($paragraphs[1]);
            } else {
                // Last resort: just take a portion of the content
                $description = $content;
            }
        }

        // If HTML formatting is requested, format the description with HTML tags
        if ($useHtmlMeta) {
            // Convert plain text to HTML with paragraphs
            $paragraphs = preg_split('/\r\n\r\n|\r\r|\n\n/', $description);
            $htmlParagraphs = array_map(function($para) {
                $para = trim($para);
                if (!empty($para)) {
                    return "<p>$para</p>";
                }
                return '';
            }, $paragraphs);

            $description = implode('', $htmlParagraphs);

            // Convert simple markdown-like formatting to HTML
            $description = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $description); // Bold
            $description = preg_replace('/\*(.*?)\*/s', '<em>$1</em>', $description); // Italic
            $description = preg_replace('/_(.*?)_/s', '<em>$1</em>', $description); // Italic

            // Convert bullet points
            $description = preg_replace('/^- (.*?)$/m', '<li>$1</li>', $description);
            $description = preg_replace('/(<li>.*?<\/li>)+/s', '<ul>$0</ul>', $description);
        }

        return $description;
    }

    /**
     * Clean up description text
     */
    private function cleanupDescription($content)
    {
        // Remove any leading/trailing whitespace
        $content = trim($content);

        // Remove any markdown headers
        $content = preg_replace('/^#+\s+.*$/m', '', $content);

        // Remove any "Description:" prefix
        $content = preg_replace('/^(?:Description|Mô tả):?\s*/i', '', $content);

        // Convert multiple line breaks to a single line break
        $content = preg_replace('/(\r\n|\r|\n){2,}/', "\n\n", $content);

        return trim($content);
    }

    /**
     * Get fallback response for demonstration or when API fails
     */
    private function getFallbackResponse($contentType)
    {
        // Sample responses for demonstration
        $responses = [
            'posts' => [
                'meta_title' => 'Giải bài tập ' . substr(str_shuffle('ABCDEFGHI'), 0, 5) . ' | Học tốt mọi môn',
                'meta_description' => 'Hướng dẫn chi tiết cách giải bài tập ' . substr(str_shuffle('ABCDEFGHI'), 0, 5) . ' với phương pháp dễ hiểu và đầy đủ. Tài liệu học tập giúp học sinh nắm vững kiến thức và cải thiện kỹ năng làm bài.'
            ],
            'chapters' => 'Chương này cung cấp kiến thức toàn diện về các khái niệm cơ bản và nâng cao. Học sinh sẽ được hướng dẫn từng bước để hiểu sâu nội dung bài học, làm quen với cách giải các bài tập từ cơ bản đến nâng cao.',
            'books' => 'Cuốn sách này là tài liệu học tập thiết yếu dành cho học sinh, được biên soạn kỹ lưỡng theo chương trình giáo dục mới nhất. Nội dung sách bao gồm lý thuyết súc tích kèm theo các ví dụ minh họa sinh động.',
            'book_groups' => 'Môn học này cung cấp nền tảng kiến thức vững chắc và kỹ năng cần thiết cho học sinh ở mọi trình độ. Chương trình được thiết kế theo chuẩn kiến thức kỹ năng của Bộ Giáo dục.',
            'generic' => 'Nội dung SEO được tối ưu với các từ khóa phù hợp, giúp tăng hiển thị trên công cụ tìm kiếm và cải thiện trải nghiệm người dùng.'
        ];

        return $responses[$contentType] ?? $responses['generic'];
    }

    /**
     * For image analysis
     */
    public function analyzeImage($imageUrl, $prompt, $options = [])
    {
        try {
            Log::debug('Starting image analysis', [
                'prompt_preview' => substr($prompt, 0, 100),
                'image_url' => $imageUrl
            ]);

            // Map model name if necessary
            $modelName = $options['model'] ?? 'grok-2-vision';
            if (isset($options['model_variant'])) {
                $modelName = $options['model_variant'];
            }
            $openRouterModel = $this->mapModelName($modelName);

            Log::debug('Mapped vision model', [
                'original' => $modelName,
                'mapped' => $openRouterModel
            ]);

            // For direct URLs, create content parts with text and image
            $messageParts = [];

            // Add text content
            $messageParts[] = new TextContentData(
                type: TextContentData::ALLOWED_TYPE,
                text: $prompt
            );

            // Add image content
            $messageParts[] = new ImageContentPartData(
                type: ImageContentPartData::ALLOWED_TYPE,
                image_url: new ImageUrlData(
                    url: $imageUrl
                )
            );

            // Prepare messages
            $messages = [];
            if (!empty($options['system_message'])) {
                $messages[] = new MessageData(
                    content: $options['system_message'],
                    role: RoleType::SYSTEM
                );

                Log::debug('Added system message for vision analysis');
            }

            // Add user message with content parts
            $messages[] = new MessageData(
                content: $messageParts,
                role: RoleType::USER
            );

            Log::debug('Creating vision ChatData', [
                'model' => $openRouterModel,
                'message_count' => count($messages)
            ]);

            // Create ChatData
            $chatData = new ChatData([
                'messages' => $messages,
                'model' => $openRouterModel,
                'temperature' => $options['temperature'] ?? 0.7
            ]);

            // Only add max_tokens if it was provided or is a reasonable value
            if (!empty($options['max_tokens']) && $options['max_tokens'] > 0) {
                $chatData->max_tokens = $options['max_tokens'];
            }

            // Make request
            Log::debug('Sending vision request to OpenRouter');
            $response = LaravelOpenRouter::chatRequest($chatData);

            Log::debug('Received vision response', [
                'has_choices' => isset($response->choices) ? 'yes' : 'no'
            ]);

            // Return content
            if (isset($response->choices) && !empty($response->choices)) {
                return $response->choices[0]->message->content ?? '';
            }

            return json_encode($response);
        } catch (\Exception $e) {
            Log::error('Vision API error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return "Unable to analyze image: " . $e->getMessage();
        }
    }
}
