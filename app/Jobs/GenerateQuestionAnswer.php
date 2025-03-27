<?php

namespace App\Jobs;

use App\Models\WikiAnswer;
use App\Models\WikiQuestion;
use App\Services\WikiAIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateQuestionAnswer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 30;

    /**
     * The question instance.
     *
     * @var WikiQuestion
     */
    protected $question;

    /**
     * Create a new job instance.
     *
     * @param WikiQuestion $question
     * @return void
     */
    public function __construct(WikiQuestion $question)
    {
        $this->question = $question;
    }

    /**
     * Execute the job.
     *
     * @param WikiAIService $aiService
     * @return void
     */
    public function handle(WikiAIService $aiService)
    {
        try {
            Log::info('Starting AI answer generation job', [
                'question_id' => $this->question->id,
                'job_id' => $this->job->getJobId() ?? 'unknown'
            ]);

            // Check if question already has an AI answer
            $existingAnswer = WikiAnswer::where('question_id', $this->question->id)
                ->where('is_ai', true)
                ->exists();

            if ($existingAnswer) {
                Log::info('Question already has an AI answer, skipping generation', [
                    'question_id' => $this->question->id
                ]);
                return;
            }

            // Generate answer content
            $content = $aiService->generateAnswer($this->question);

            // Save the answer
            $answer = new WikiAnswer();
            $answer->question_id = $this->question->id;
            $answer->content = $content;
            $answer->is_ai = true;
            $answer->save();

            // Update question status to published if it's pending
            if ($this->question->status === 'pending') {
                // Check if automatic approval is enabled
                $autoApprove = (bool) \App\Models\WikiSetting::get('approve_questions_automatic', '0');

                if ($autoApprove) {
                    $this->question->status = 'published';
                    $this->question->save();

                    // Trigger event for status change if needed
                    app(\App\Services\WikiEventService::class)->onQuestionStatusChanged(
                        $this->question,
                        'pending',
                        'published'
                    );
                }
            }

            Log::info('AI answer generation completed successfully', [
                'question_id' => $this->question->id,
                'answer_id' => $answer->id
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating AI answer: ' . $e->getMessage(), [
                'question_id' => $this->question->id,
                'trace' => $e->getTraceAsString()
            ]);

            // Release the job to be retried later if within retry limit
            if ($this->attempts() < $this->tries) {
                $this->release($this->backoff);
            } else {
                // If max retries reached, try to create a fallback answer
                try {
                    $this->createFallbackAnswer();
                } catch (\Exception $fallbackError) {
                    Log::error('Error creating fallback answer: ' . $fallbackError->getMessage(), [
                        'question_id' => $this->question->id
                    ]);
                }
            }
        }
    }

    /**
     * Create a fallback answer when AI generation fails.
     *
     * @return void
     */
    protected function createFallbackAnswer()
    {
        $categoryName = $this->question->category ? $this->question->category->name : 'Uncategorized';

        $fallbackContent = <<<EOT
<h2>Câu trả lời cho: {$this->question->title}</h2>

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

        // Create fallback answer
        $answer = new WikiAnswer();
        $answer->question_id = $this->question->id;
        $answer->content = $fallbackContent;
        $answer->is_ai = true;
        $answer->save();

        Log::info('Created fallback answer after AI generation failed', [
            'question_id' => $this->question->id,
            'answer_id' => $answer->id
        ]);
    }
}
