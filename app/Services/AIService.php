<?php

namespace App\Services;

use GrokPHP\Laravel\Facades\GrokAI;
use GrokPHP\Client\Config\ChatOptions;
use GrokPHP\Client\Enums\Model as GrokModel;
use GrokPHP\Client\Exceptions\GrokException;
use DeepSeek\DeepSeekClient;
use Illuminate\Support\Facades\Log;

class AIService
{
    protected $deepseekClient;

    public function __construct(DeepSeekClient $deepseekClient)
    {
        $this->deepseekClient = $deepseekClient;
    }

    /**
     * Call AI model to generate content
     *
     * @param string $model The model to use (grok-2, grok-2-latest, deepseek-v3)
     * @param string $prompt The prompt to send to the model
     * @param array $options Additional options for the API call
     *
     * @return string|array The generated content
     */
    public function generate($model, $prompt, $options = [])
    {
        try {
            // Add formatting instructions via system message if not already provided
            if (empty($options['system_message'])) {
                $options['system_message'] = $this->getFormattingSystemMessage(
                    $options['content_type'] ?? 'generic',
                    $options['use_html_meta'] ?? false // Pass the HTML option
                );
            }

            if (strpos($model, 'grok') === 0) {
                return $this->callGrokModel($prompt, $model, $options);
            } elseif (strpos($model, 'deepseek') === 0) {
                return $this->callDeepseekModel($prompt, $options);
            } else {
                throw new \Exception("Unsupported model: $model");
            }
        } catch (\Exception $e) {
            Log::error('AI generation error', [
                'model' => $model,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->getFallbackResponse($options['content_type'] ?? 'generic');
        }
    }

    /**
     * Get system message with formatting instructions based on content type
     */
    private function getFormattingSystemMessage($contentType, $useHtmlMeta = false)
    {
        // Base system messages with common instructions
        $baseMessages = [
            'posts' => "You are an SEO specialist for educational content. Format your response EXACTLY as follows:
Meta Title: [Your title here - maximum 60 characters]
Meta Description: [Your description here]

Do not include any other text, explanations, or formatting. Just provide the Meta Title and Meta Description with these exact labels.",

            'chapters' => "You are an educational content writer. Provide a clear, informative description for a book chapter.
Format your response as plain text paragraphs with no headers, prefixes or labels.
Write 2-3 concise paragraphs that explain what students will learn from this chapter.",

            'books' => "You are an educational content specialist. Provide a comprehensive description for an educational book.
Format your response as plain text paragraphs with no headers, prefixes or labels.
Write 3-4 paragraphs explaining the educational value of the book, the target audience, and key learning outcomes.",

            'book_groups' => "You are a curriculum specialist. Provide a description for an educational subject/course.
Format your response as plain text paragraphs with no headers, prefixes or labels.
Write 2-3 paragraphs explaining what students will learn in this subject, focusing on educational benefits and skills developed.",

            'generic' => "Format your response clearly and consistently. If providing metadata, use explicit labels like 'Meta Title:' and 'Meta Description:'."
        ];

        // Add HTML formatting instructions for posts when requested
        if ($useHtmlMeta && $contentType === 'posts') {
            $baseMessages['posts'] = "You are an SEO specialist for educational content. Format your response EXACTLY as follows:
Meta Title: [Your title here - maximum 60 characters]
Meta Description: [Your description here]

For the Meta Description, use basic HTML formatting:
- Use <p> tags for paragraphs
- Use <strong> for important terms or emphasis
- Use <em> for light emphasis
- Use <ul> and <li> for lists
- Keep the HTML clean and simple

Do not include any other text, explanations, or formatting. Just provide the Meta Title and Meta Description with these exact labels.";
        }

        return $baseMessages[$contentType] ?? $baseMessages['generic'];
    }

    /**
     * Call Grok model API
     */
    private function callGrokModel($prompt, $modelName, $options = [])
    {
        // Convert model name to Grok enum
        $grokModel = $this->getGrokModelEnum($modelName);

        // Prepare messages - formatting as expected by Grok API
        $messages = [];

        // Add system message if available
        if (!empty($options['system_message'])) {
            $messages[] = ['role' => 'system', 'content' => $options['system_message']];
        }

        // Add user prompt
        $messages[] = ['role' => 'user', 'content' => $prompt];

        // Create chat options
        $chatOptions = new ChatOptions(
            model: $grokModel,
            stream: false,
            temperature: $options['temperature'] ?? 0.7
        );

        try {
            // Call the Grok API
            $response = GrokAI::chat($messages, $chatOptions);

            // Extract response content
            $content = $response->content();

            // Post-process the content based on content type
            return $this->processResponse($content, $options['content_type'] ?? 'generic');
        } catch (GrokException $e) {
            Log::error('Grok API error', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Call DeepSeek model API
     */
    private function callDeepseekModel($prompt, $options = [])
    {
        $contentType = $options['content_type'] ?? 'generic';
        $temperature = $options['temperature'] ?? 0.7;
        $model = $options['model_variant'] ?? 'deepseek-chat';

        try {
            // Create DeepSeek client with specified options
            $client = $this->deepseekClient
                ->withModel($model)
                ->setTemperature($temperature);

            // Add system message if available
            if (!empty($options['system_message'])) {
                $client->query($options['system_message'], 'system');
            }

            // Add user prompt
            $client->query($prompt, 'user');

            // Execute the query
            $response = $client->run();

            // Process the response based on content type
            return $this->processResponse($response, $contentType);
        } catch (\Exception $e) {
            Log::error('DeepSeek API error', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Convert model name to Grok model enum
     */
    private function getGrokModelEnum($modelName)
    {
        return match ($modelName) {
            'grok-2' => GrokModel::GROK_2,
            'grok-2-latest' => GrokModel::GROK_2_LATEST,
            'grok-2-1212' => GrokModel::GROK_2_1212,
            'grok-2-vision' => GrokModel::GROK_2_VISION,
            'grok-2-vision-latest' => GrokModel::GROK_2_VISION_LATEST,
            default => GrokModel::GROK_2,
        };
    }

    /**
     * Process and format response based on content type
     */
    private function processResponse($content, $contentType, $options = [])
    {
        $useHtmlMeta = $options['use_html_meta'] ?? false;

        switch ($contentType) {
            case 'posts':
                // Extract meta title and description from the response
                $metaTitle = $this->extractMetaTitle($content);
                $metaDescription = $this->extractMetaDescription($content, $useHtmlMeta);

                return [
                    'meta_title' => $metaTitle,
                    'meta_description' => $metaDescription
                ];

            case 'chapters':
            case 'books':
            case 'book_groups':
                // For these types, the entire response is the description
                return $this->cleanupDescription($content);

            default:
                return $content;
        }
    }

    /**
     * Extract meta title from response
     */
    private function extractMetaTitle($content)
    {
        // First try to extract using markdown or specific formatting
        if (preg_match('/meta title:?\s*([^\n]+)/i', $content, $matches)) {
            return trim($matches[1]);
        }

        if (preg_match('/title:?\s*([^\n]+)/i', $content, $matches)) {
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
        if (preg_match('/meta description:?\s*([^\n]+(\n[^#\n][^\n]*)*)/i', $content, $matches)) {
            $description = trim($matches[1]);
        } elseif (preg_match('/description:?\s*([^\n]+(\n[^#\n][^\n]*)*)/i', $content, $matches)) {
            $description = trim($matches[1]);
        } elseif (preg_match('/2\.\s*([^\n]+(\n[^#\n][^\n]*)*)/i', $content, $matches)) {
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
        $content = preg_replace('/^Description:?\s*/i', '', $content);

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
     * For image analysis (if needed)
     */
    public function analyzeImage($imageUrl, $prompt, $options = [])
    {
        try {
            $useDeepseek = $options['use_deepseek'] ?? false;

            if ($useDeepseek && isset($options['deepseek_model'])) {
                // If DeepSeek supports image analysis
                $client = $this->deepseekClient
                    ->withModel($options['deepseek_model'])
                    ->setTemperature($options['temperature'] ?? 0.7);

                if (!empty($options['system_message'])) {
                    $client->query($options['system_message'], 'system');
                }

                // Assuming DeepSeek has a method for image analysis
                $client->queryWithImage($imageUrl, $prompt);
                $response = $client->run();

                return $response;
            } else {
                // Use Grok for image analysis
                $response = GrokAI::vision()->analyze($imageUrl, $prompt);
                return $response->content();
            }
        } catch (\Exception $e) {
            Log::error('Vision API error', [
                'error' => $e->getMessage()
            ]);
            return "Unable to analyze image: " . $e->getMessage();
        }
    }
}
