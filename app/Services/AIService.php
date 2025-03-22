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

            // Prepare system message if provided
            $messages = [];
            if (!empty($options['system_message'])) {
                $messages[] = new MessageData(
                    content: $options['system_message'],
                    role: RoleType::SYSTEM
                );
            }

            $prompt = $this->processPrompt($prompt, $options['content_type'] ?? 'generic', $useHtmlMeta);

            // Add user message
            $messages[] = new MessageData(
                content: $prompt,
                role: RoleType::USER
            );

            // Prepare parameters for ChatData with ONLY the required fields
            $chatDataParams = [
                'messages' => $messages,
                'model' => $openRouterModel
            ];

            // Add optional parameters EXCEPT ones that conflict with XOR validation
            $allowedParams = [
                'temperature', 'top_p', 'frequency_penalty',
                'presence_penalty', 'stop', 'max_tokens'
            ];

            foreach ($allowedParams as $param) {
                if (isset($options[$param]) && !empty($options[$param])) {
                    $chatDataParams[$param] = $options[$param];
                }
            }

            // IMPORTANT: Use fromArray to avoid DTO validation issues with properties
            // that aren't explicitly set in the constructor
            $chatData = ChatData::from($chatDataParams);

            // Handle structured output if requested
            if (!empty($options['use_html_meta'])) {
                $chatData->response_format = new ResponseFormatData(
                    type: 'json_object'
                );

                $chatData->provider = new ProviderPreferencesData(
                    require_parameters: true
                );

                Log::debug('Added structured output format for HTML meta');
            }

            // Make the request
            $response = LaravelOpenRouter::chatRequest($chatData);

            // Process the response
            $processedResponse = $this->processResponse($response, $options['content_type'] ?? 'generic', $options);

            return $processedResponse;
        } catch (\Exception $e) {
            Log::error('AI generation error', [
                'model' => $model,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return $this->getFallbackResponse($options['content_type'] ?? 'generic');
        }
    }

    /**
     * Process and format prompt to guide AI model's response format
     *
     * @param string $prompt The original prompt
     * @param string $contentType Type of content (posts, chapters, books, book_groups)
     * @param bool $useHtmlMeta Whether to use HTML formatting
     * @return string The enhanced prompt with formatting instructions
     */
    private function processPrompt($prompt, $contentType, $useHtmlMeta = false)
    {
        // Temporary: We disable $useHtmlMeta because there are issues with the response formatter
        $useHtmlMeta = false;

        // Define the role and intention clearly based on content type
        $roleInstruction = match($contentType) {
            'posts' => "Bạn là chuyên gia viết nội dung giáo dục với kinh nghiệm trong việc tạo bài giới thiệu chi tiết cho các bài học.",
            'chapters' => "Bạn là chuyên gia giáo dục với kinh nghiệm viết tổng quan về các chương sách giáo khoa.",
            'books' => "Bạn là chuyên gia biên soạn sách giáo dục, có khả năng tạo nội dung giới thiệu sách học tập.",
            'book_groups' => "Bạn là chuyên gia phân tích chương trình giảng dạy với khả năng tạo nội dung giới thiệu bộ sách giáo dục.",
            default => "Bạn là chuyên gia tạo nội dung giáo dục chất lượng cao với hiểu biết sâu sắc về chủ đề.",
        };

        // Create structure guidelines based on content type
        $contentStructure = match($contentType) {
            'posts' => "Hãy viết một bài giới thiệu chi tiết (khoảng 800-1000 từ) về bài học này bao gồm các phần sau:

1. **Tổng quan về bài học**: Giới thiệu chủ đề và mục tiêu chính
2. **Kiến thức và kỹ năng**: Những gì học sinh sẽ học được 
3. **Phương pháp tiếp cận**: Cách thức bài học được tổ chức
4. **Ứng dụng thực tế**: Cách áp dụng kiến thức vào thực tế
5. **Kết nối với chương trình học**: Mối liên hệ với các bài học khác
6. **Hướng dẫn học tập**: Gợi ý phương pháp học hiệu quả",

            'chapters' => "Hãy viết một bài giới thiệu tổng quan (khoảng 800-1000 từ) về chương sách này bao gồm các phần sau:

1. **Giới thiệu chương**: Nội dung và mục tiêu chính
2. **Các bài học chính**: Tổng quan về các bài học trong chương
3. **Kỹ năng phát triển**: Những kỹ năng học sinh sẽ đạt được
4. **Khó khăn thường gặp**: Những thách thức học sinh có thể gặp phải
5. **Phương pháp tiếp cận**: Gợi ý cách tiếp cận học tập hiệu quả
6. **Liên kết kiến thức**: Mối liên hệ với các chương khác",

            'books' => "Hãy viết một bài giới thiệu chi tiết (khoảng 800-1000 từ) về cuốn sách này bao gồm các phần sau:

1. **Tổng quan sách**: Mục đích và đối tượng sử dụng
2. **Cấu trúc nội dung**: Các phần và chương chính
3. **Phương pháp giảng dạy**: Cách tiếp cận giáo dục của sách
4. **Đặc điểm nổi bật**: Điểm mạnh và tính năng đặc biệt
5. **Hỗ trợ học tập**: Các công cụ và tài nguyên đi kèm
6. **Hướng dẫn sử dụng**: Cách sử dụng sách hiệu quả nhất",

            'book_groups' => "Hãy viết một bài giới thiệu tổng quan (khoảng 800-1000 từ) về bộ sách này bao gồm các phần sau:

1. **Giới thiệu bộ sách**: Mục đích và tầm nhìn giáo dục
2. **Đối tượng học sinh**: Phù hợp với những học sinh nào
3. **Cấu trúc chương trình**: Các sách và mối liên hệ giữa chúng
4. **Phương pháp giáo dục**: Cách tiếp cận dạy và học
5. **Lợi ích chính**: Giá trị mang lại cho học sinh và giáo viên
6. **Cách sử dụng hiệu quả**: Hướng dẫn tận dụng tối đa bộ sách",

            default => "Hãy viết một bài nội dung chi tiết (khoảng 800-1000 từ) với cấu trúc rõ ràng, bố cục mạch lạc và thông tin đầy đủ.",
        };

        // HTML formatting instructions when needed
        $formattingInstructions = $useHtmlMeta ? 
"ĐỊNH DẠNG HTML: Bài viết cần được định dạng bằng các thẻ HTML cơ bản để hiển thị trên website:

1. Sử dụng thẻ `<h2>` cho các tiêu đề chính 
2. Sử dụng thẻ `<h3>` cho các tiêu đề phụ
3. Sử dụng thẻ `<p>` cho mỗi đoạn văn
4. Sử dụng thẻ `<strong>` cho văn bản quan trọng cần nhấn mạnh
5. Sử dụng thẻ `<em>` cho văn bản cần in nghiêng
6. Sử dụng thẻ `<ul>` và `<li>` cho danh sách không có thứ tự
7. Sử dụng thẻ `<ol>` và `<li>` cho danh sách có thứ tự

Đảm bảo mỗi thẻ đều được đóng đúng cách. Không sử dụng các thẻ HTML phức tạp khác. Không thêm các thuộc tính CSS hoặc JavaScript." : 

"ĐỊNH DẠNG VĂN BẢN: Hãy viết với cấu trúc rõ ràng, sử dụng tiêu đề, đoạn văn và danh sách để tạo bố cục mạch lạc.";

        // Strict output format requirements to avoid JSON artifacts
        $preciseOutputInstructions = "
HƯỚNG DẪN QUAN TRỌNG VỀ KẾT QUẢ ĐẦU RA:

1. KHÔNG thêm bất kỳ phần mở đầu thừa nào như 'Dưới đây là bài viết' hoặc 'Tôi sẽ viết'.
2. KHÔNG thêm bất kỳ phần kết thúc thừa nào như 'Tôi hy vọng bài viết này hữu ích'.
3. KHÔNG bao gồm bất kỳ dấu ngoặc JSON, ký hiệu đặc biệt, hay chuỗi như 'refusal:null' trong nội dung.
4. BẮT ĐẦU và KẾT THÚC phản hồi của bạn chính xác với nội dung bài viết, không có văn bản thừa.
5. Viết hoàn toàn bằng tiếng Việt với ngữ pháp và chính tả chuẩn mực.

<START_CONTENT>

[Đặt nội dung bài viết chính xác ở đây, không có văn bản thừa]

<END_CONTENT>

BẠN CHỈ ĐƯỢC TRẢ VỀ NỘI DUNG GIỮA CÁC THẺ START_CONTENT VÀ END_CONTENT, KHÔNG ĐƯỢC BAO GỒM CÁC THẺ NÀY!";

        // Combine everything into the final prompt
        $enhancedPrompt = $roleInstruction . "\n\n" . 
                         $contentStructure . "\n\n" . 
                         $formattingInstructions . "\n\n" . 
                         $preciseOutputInstructions . "\n\n" . 
                         $prompt;

        return $enhancedPrompt;
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

    /**
     * Clean up response artifacts from AI model output
     *
     * @param string $content The raw content from the AI model
     * @return string Cleaned content
     */
    private function cleanResponseArtifacts($content)
    {
        if (!is_string($content)) {
            return $content;
        }
        
        // Remove JSON artifacts like ","refusal":null}}
        $content = preg_replace('/",\s*"refusal"\s*:\s*null\s*\}\s*\}.*$/s', '', $content);
        $content = preg_replace('/"\s*,\s*".*?\}\s*\}.*$/s', '', $content);
        
        // Remove any trailing JSON that might be included
        $content = preg_replace('/\}\s*\}.*$/s', '', $content);
        
        // Remove any other JSON-like artifacts
        $content = preg_replace('/"?\s*\}\s*\]?\s*"?\s*$/s', '', $content);
        
        // Remove any opening JSON structure that might be included
        $content = preg_replace('/^\s*\{\s*"[^"]+"\s*:\s*\{/s', '', $content);
        
        // Remove any escaped quotes at the beginning or end
        $content = preg_replace('/^"(.*)"$/s', '$1', $content);
        
        // Replace escaped newlines with actual newlines
        $content = str_replace('\\n', "\n", $content);
        
        // Unescape characters
        $content = stripcslashes($content);
        
        return $content;
    }

    /**
     * Ensure HTML is valid and properly formatted
     *
     * @param string $content The HTML content to validate
     * @return string Valid HTML content
     */
    private function ensureValidHtml($content)
    {
        // If content doesn't have HTML tags, add basic paragraph tags
        if (strpos($content, '<') === false) {
            return '<p>' . str_replace("\n\n", '</p><p>', $content) . '</p>';
        }
        
        // Check for common HTML issues
        
        // 1. Missing paragraph tags around text
        if (!preg_match('/<p>/i', $content)) {
            $parts = preg_split('/(<h[1-6].*?>.*?<\/h[1-6]>|<ul>.*?<\/ul>|<ol>.*?<\/ol>)/is', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
            $result = '';
            
            foreach ($parts as $part) {
                if (preg_match('/^<h[1-6]|^<ul|^<ol/i', $part)) {
                    // This is already a heading or list, keep as is
                    $result .= $part;
                } elseif (trim($part) != '') {
                    // This is text that needs paragraph tags
                    $paragraphs = preg_split('/\n\n+/', trim($part));
                    foreach ($paragraphs as $paragraph) {
                        if (trim($paragraph) != '') {
                            $result .= '<p>' . trim($paragraph) . '</p>';
                        }
                    }
                }
            }
            
            $content = $result;
        }
        
        // 2. Ensure all tags are properly closed
        $openingTags = [
            '<p>' => '</p>',
            '<h2>' => '</h2>',
            '<h3>' => '</h3>',
            '<strong>' => '</strong>',
            '<em>' => '</em>',
            '<ul>' => '</ul>',
            '<ol>' => '</ol>',
            '<li>' => '</li>'
        ];
        
        foreach ($openingTags as $openTag => $closeTag) {
            $openCount = substr_count(strtolower($content), strtolower($openTag));
            $closeCount = substr_count(strtolower($content), strtolower($closeTag));
            
            // Add missing closing tags if needed
            if ($openCount > $closeCount) {
                $content .= str_repeat($closeTag, $openCount - $closeCount);
            }
        }
        
        return $content;
    }

    /**
     * Process and format response based on content type
     *
     * @param mixed $response The response from OpenRouter
     * @param string $contentType Type of content
     * @param array $options Additional options
     * @return string|array The formatted and processed content
     */
    private function processResponse($response, $contentType, $options = [])
    {
        try {
            // Extract content from different possible response formats
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
                    'response_keys' => is_object($response) ? array_keys((array)$response) : 'not_object'
                ]);
                
                // Try to extract content from the first level if choices is missing
                $content = $response->content ?? json_encode($response);
            }

            // Clean up any JSON artifacts or unwanted parts
            $content = $this->cleanResponseArtifacts($content);

            switch ($contentType) {
                case 'posts':
                    // For articles, we should already have the full HTML content
                    if ($options['use_html_meta'] ?? false) {
                        // Ensure the HTML is properly formatted
                        $content = $this->ensureValidHtml($content);
                    }
                    
                    return $content;

                case 'chapters':
                case 'books':
                case 'book_groups':
                    // For these types, clean any remaining artifacts
                    $content = $this->cleanupDescription($content);
                    return $content;

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