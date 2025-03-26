<?php

namespace App\Services;

use App\Models\WikiQuestion;
use App\Models\WikiAnswer;
use App\Models\WikiQuestionEmbedding;
use App\Models\WikiSetting;
use App\Services\AI\AIServiceFactory;
use Illuminate\Support\Facades\Log;

class WikiAIService
{
    /**
     * Process a question and generate an AI answer.
     */
    public function processQuestion(WikiQuestion $question): void
    {
        try {
            // Generate embeddings for vector search
            $this->generateEmbedding($question);

            // Generate the answer
            $content = $this->generateAnswer($question);

            // Save the answer
            $answer = new WikiAnswer();
            $answer->question_id = $question->id;
            $answer->content = $content;
            $answer->is_ai = true;
            $answer->save();

            // Update question status to published
            $question->status = 'published';
            $question->save();
        } catch (\Exception $e) {
            Log::error('Error processing question: ' . $e->getMessage(), [
                'question_id' => $question->id,
                'exception' => $e,
            ]);
            throw $e;
        }
    }

    /**
     * Generate an embedding for a question.
     */
    private function generateEmbedding(WikiQuestion $question): void
    {
        $text = $question->title . ' ' . strip_tags($question->content);
        $embedding = $this->generateEmbeddingForText($text);

        if (!empty($embedding)) {
            WikiQuestionEmbedding::updateOrCreate(
                ['question_id' => $question->id],
                ['embedding' => json_encode($embedding)]
            );
        }
    }

    /**
     * Generate embedding vector for a text.
     */
    public function generateEmbeddingForText(string $text): ?array
    {
        try {
            $providerName = $this->getAIProvider();
            $embeddingModel = $this->getEmbeddingModel($providerName);
            $service = AIServiceFactory::createService($providerName);

            $response = $service->generate($embeddingModel, $text, [
                'purpose' => 'embedding',
                'content_type' => 'embedding',
            ]);

            $embedding = $this->extractEmbeddingFromResponse($response);

            if (empty($embedding)) {
                Log::warning('Using fallback random embedding generation');
                $embedding = $this->generateRandomEmbedding();
            }

            return $embedding;
        } catch (\Exception $e) {
            Log::error('Error generating embedding: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            return $this->generateRandomEmbedding();
        }
    }

    /**
     * Generate a random embedding for development/testing purposes.
     */
    private function generateRandomEmbedding(): array
    {
        $embedding = [];
        for ($i = 0; $i < 1536; $i++) {
            $embedding[] = (float) mt_rand(-100, 100) / 100;
        }
        return $embedding;
    }

    /**
     * Extract embedding vector from AI service response.
     */
    private function extractEmbeddingFromResponse($response): ?array
    {
        if (is_array($response) && isset($response['embedding'])) {
            return $response['embedding'];
        }

        if (is_array($response) && isset($response['choices']) &&
            isset($response['choices'][0]['message']['content'])) {

            $content = $response['choices'][0]['message']['content'];
            if (is_string($content)) {
                $decoded = json_decode($content, true);
                if (is_array($decoded) && isset($decoded['embedding']) &&
                    is_array($decoded['embedding'])) {
                    return $decoded['embedding'];
                }
            }
        }

        return null;
    }

    /**
     * Generate a SEO-friendly title for a question based on its content.
     *
     * @param string $content The question content
     * @param string|null $categoryName The category name (optional)
     * @param string|null $bookGroupName The book group name (optional)
     * @return string The generated title
     */
    public function generateQuestionTitle(string $content, ?string $categoryName = null, ?string $bookGroupName = null): string
    {
        try {
            $providerName = $this->getAIProvider();
            $model = $this->getAnswerModel($providerName);
            $service = AIServiceFactory::createService($providerName);

            // Strip HTML tags and extract text
            $textContent = strip_tags($content);

            // Truncate content if it's too long
            if (mb_strlen($textContent) > 1000) {
                $textContent = mb_substr($textContent, 0, 1000) . '...';
            }

            // Build prompt for title generation
            $prompt = $this->buildTitleGenerationPrompt($textContent, $categoryName, $bookGroupName);

            $options = [
                'temperature' => 0.7,
                'max_tokens' => 100,
                'content_type' => 'wiki_title',
            ];

            $response = $service->generate($model, $prompt, $options);

            // Extract title from response
            $title = $this->extractTitleFromResponse($response);

            // If title generation failed, use a fallback method
            if (empty($title)) {
                return $this->generateFallbackTitle($textContent);
            }

            // Validate and clean up the title
            $title = $this->cleanupTitle($title);

            return $title;
        } catch (\Exception $e) {
            Log::error('Error generating question title: ' . $e->getMessage(), [
                'content_preview' => mb_substr(strip_tags($content), 0, 100),
                'exception' => $e,
            ]);

            return $this->generateFallbackTitle($content);
        }
    }

    /**
     * Build a prompt for title generation.
     */
    private function buildTitleGenerationPrompt(string $content, ?string $categoryName, ?string $bookGroupName): string
    {
        $contextInfo = '';
        if ($categoryName) {
            $contextInfo .= "Danh mục: {$categoryName}\n";
        }
        if ($bookGroupName) {
            $contextInfo .= "Bộ sách: {$bookGroupName}\n";
        }

        return <<<EOT
Bạn là một chuyên gia SEO. Hãy tạo một tiêu đề ngắn gọn, hấp dẫn, và tối ưu SEO cho câu hỏi dưới đây.
Tiêu đề nên:
- Ngắn gọn (dưới 70 ký tự)
- Chứa từ khóa chính
- Có tính mô tả cao
- Thu hút người đọc
- Sử dụng tiếng Việt chuẩn, dễ hiểu
- KHÔNG chứa dấu "?" ở cuối câu trừ khi tiêu đề là một câu hỏi thực sự

{$contextInfo}

NỘI DUNG CÂU HỎI:
{$content}

CHỈ TRẢ VỀ TIÊU ĐỀ, KHÔNG CÓ GIẢI THÍCH HAY THÔNG TIN THÊM.
EOT;
    }

    /**
     * Extract title from AI service response.
     */
    private function extractTitleFromResponse($response): ?string
    {
        if (is_array($response) && isset($response['choices']) &&
            isset($response['choices'][0]['message']['content'])) {

            $content = $response['choices'][0]['message']['content'];

            // Clean up the content (remove quotes, extra whitespace, etc.)
            $content = trim($content);
            $content = preg_replace('/^["\']+|["\']+$/', '', $content);
            $content = preg_replace('/\s+/', ' ', $content);

            return $content;
        }

        return null;
    }

    /**
     * Generate a fallback title if AI generation fails.
     */
    private function generateFallbackTitle(string $content): string
    {
        // Strip HTML and trim
        $text = strip_tags($content);
        $text = trim($text);

        // Get the first sentence or part of text
        $sentences = preg_split('/(?<=[.?!])\s+/', $text, 2, PREG_SPLIT_NO_EMPTY);
        $firstPart = isset($sentences[0]) ? $sentences[0] : $text;

        // Limit to 70 characters
        if (mb_strlen($firstPart) > 70) {
            $firstPart = mb_substr($firstPart, 0, 67) . '...';
        }

        return $firstPart;
    }

    /**
     * Clean up and validate a generated title.
     */
    private function cleanupTitle(string $title): string
    {
        // Strip any HTML
        $title = strip_tags($title);

        // Trim whitespace
        $title = trim($title);

        // Remove quotes if they wrap the entire title
        $title = preg_replace('/^["\'](.*)["\']$/', '$1', $title);

        // Remove any "Title:" prefix that AI might add
        $title = preg_replace('/^(Tiêu đề:|Title:)\s*/i', '', $title);

        // Ensure the title isn't too long (max 100 chars)
        if (mb_strlen($title) > 100) {
            $title = mb_substr($title, 0, 97) . '...';
        }

        // If empty or too short, use a generic title
        if (empty($title) || mb_strlen($title) < 5) {
            $title = 'Câu hỏi mới từ người dùng';
        }

        return $title;
    }

    /**
     * Generate an answer for a question.
     */
    public function generateAnswer(WikiQuestion $question, bool $stream = false): string
    {
        try {
            Log::debug('Generating answer for question: ' . $question->id);

            $providerName = $this->getAIProvider();
            $model = $this->getAnswerModel($providerName);
            $service = AIServiceFactory::createService($providerName);
            $prompt = $this->buildQuestionPrompt($question);

            $options = [
                'temperature' => 0.7,
                'max_tokens' => 2048,
                'content_type' => 'wiki_answer',
                'stream' => false, // Always set to false - we'll handle streaming in the controller
            ];

            // Get response from AI service
            $response = $service->generate($model, $prompt, $options);

            // Log the response type and preview
            Log::debug('Raw AI response type: ' . gettype($response));
            if (is_array($response)) {
                Log::debug('Response array keys: ' . implode(', ', array_keys($response)));
            }

            // Extract content from the response
            $content = '';
            if (is_array($response) && isset($response['choices']) && isset($response['choices'][0]['message']['content'])) {
                $content = $response['choices'][0]['message']['content'];
            } else {
                // Try other possible formats
                if (is_array($response) && isset($response['content'])) {
                    $content = $response['content'];
                } elseif (is_object($response) && method_exists($response, 'content')) {
                    $content = $response->content();
                } elseif (is_string($response)) {
                    $content = $response;
                } else {
                    Log::warning('Unrecognized response format from AI service');
                    $content = json_encode($response);
                }
            }

            // Clean the content of any special tags
            $content = preg_replace([
                '/<START_CONTENT>/',
                '/<END_CONTENT>/',
                '/```html/',
                '/```/'
            ], '', $content);

            // Check if content is empty after cleaning
            if (empty(trim($content))) {
                Log::warning('Empty content after extraction/cleaning');
                return $this->getFallbackAnswer($question);
            }

            // Log content preview for debugging
            Log::debug('Extracted content preview: ' . substr($content, 0, 200));

            return $content;
        } catch (\Exception $e) {
            Log::error('Error generating answer: ' . $e->getMessage(), [
                'question_id' => $question->id,
                'exception' => $e,
            ]);

            return $this->getFallbackAnswer($question);
        }
    }

    /**
     * Generate a streamed answer for a question.
     * This just wraps generateAnswer now - we don't actually do streaming here
     */
    public function generateStreamedAnswer(WikiQuestion $question): string
    {
        return $this->generateAnswer($question, false);
    }

    /**
     * Build a prompt for generating an answer to the question.
     */
    private function buildQuestionPrompt(WikiQuestion $question): string
    {
        $categoryName = $question->category ? $question->category->name : 'Uncategorized';
        $bookGroupName = $question->bookGroup ? $question->bookGroup->name : 'General';

        return <<<EOT
Bạn là một trợ lý đánh giá cao về tính chính xác và hữu ích. Hãy trả lời câu hỏi dưới đây một cách chi tiết, rõ ràng và dễ hiểu.
Sử dụng định dạng HTML để cấu trúc câu trả lời. Đảm bảo bao gồm các ví dụ, giải thích, và thông tin bổ sung nếu cần thiết.

THÔNG TIN VỀ CÂU HỎI:
Tiêu đề: {$question->title}
Nội dung: {$question->content}
Danh mục: {$categoryName}
Bộ sách: {$bookGroupName}

Hãy trả lời câu hỏi trên một cách chi tiết và dễ hiểu. Sử dụng các thẻ HTML (h2, h3, p, ul, li, strong, em) để định dạng câu trả lời của bạn.
Câu trả lời nên có giá trị giáo dục cao, chính xác và toàn diện.
EOT;
    }

    /**
     * Provide a fallback answer when AI generation fails.
     */
    private function getFallbackAnswer(WikiQuestion $question): string
    {
        $categoryName = $question->category ? $question->category->name : 'Uncategorized';

        return <<<EOT
<h2>Câu trả lời cho: {$question->title}</h2>

<p>Chúng tôi đang xử lý câu hỏi của bạn. Đây là một câu trả lời tạm thời.</p>

<p>Câu hỏi của bạn thuộc danh mục <strong>{$categoryName}</strong>. Một câu trả lời đầy đủ đang được xây dựng và sẽ sớm được cập nhật.</p>

<h3>Các bước tiếp theo</h3>
<ul>
    <li>Kiểm tra lại sau vài phút để xem câu trả lời đầy đủ</li>
    <li>Bạn có thể bổ sung thêm thông tin vào câu hỏi nếu cần</li>
    <li>Xem các câu hỏi liên quan trong cùng danh mục</li>
</ul>

<p>Cảm ơn bạn đã sử dụng hệ thống hỏi đáp của chúng tôi!</p>
EOT;
    }

    /**
     * Simulate streaming of content for fallback.
     * @return array Array of content chunks
     */
    private function simulateStreaming(string $content): array
    {
        return str_split($content, 40); // Return chunks instead of echoing
    }

    /**
     * Get the configured AI provider from settings.
     */
    private function getAIProvider(): string
    {
        $aiService = new AIService();
        $defaultProvider = $aiService->getDefaultProvider();

        return WikiSetting::get('default_ai_provider', $defaultProvider);
    }

    /**
     * Get the appropriate embedding model for the provider.
     */
    private function getEmbeddingModel(string $provider): string
    {
        $modelMap = [
            'google-gemini' => 'gemini-2.0-flash-lite',
            'xai-grok' => 'grok-2',
            'openrouter' => 'meta-llama/llama-3.2-1b-instruct:free'
        ];

        return $modelMap[$provider] ?? 'meta-llama/llama-3.2-1b-instruct:free';
    }

    /**
     * Get the appropriate answer generation model for the provider.
     */
    private function getAnswerModel(string $provider): string
    {
        $modelMap = [
            'google-gemini' => 'gemini-2.0-flash-lite',
            'xai-grok' => 'grok-2',
            'openrouter' => 'mistralai/mistral-small-3.1-24b-instruct:free'
        ];

        return $modelMap[$provider] ?? 'mistralai/mistral-small-3.1-24b-instruct:free';
    }

    /**
     * Generate embeddings for a question and store them in the database.
     */
    public function generateEmbeddingForQuestion(WikiQuestion $question): ?WikiQuestionEmbedding
    {
        if ($question->embedding) {
            return $question->embedding;
        }

        $text = $question->title . "\n" . strip_tags($question->content);
        $embedding = $this->generateEmbeddingForText($text);

        if (!$embedding) {
            Log::error('Failed to generate embedding for question: ' . $question->id);
            return null;
        }

        return WikiQuestionEmbedding::create([
            'question_id' => $question->id,
            'embedding' => $embedding,
        ]);
    }
}
