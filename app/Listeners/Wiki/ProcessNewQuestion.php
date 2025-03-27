<?php

namespace App\Listeners\Wiki;

use App\Events\Wiki\QuestionCreated;
use App\Jobs\GenerateQuestionAnswer;
use App\Models\WikiSetting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ProcessNewQuestion implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The time (seconds) before the job should be processed.
     *
     * @var int
     */
    public $delay = 10;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  QuestionCreated  $event
     * @return void
     */
    public function handle(QuestionCreated $event)
    {
        try {
            $question = $event->question;

            Log::info('Processing new question event', [
                'question_id' => $question->id,
                'title' => $question->title,
                'user_id' => $question->user_id
            ]);

            // Check if we should automatically generate an answer
            $shouldAutoGenerate = (bool) WikiSetting::get('auto_generate_answers', '1');

            if ($shouldAutoGenerate) {
                // Dispatch job to generate answer
                GenerateQuestionAnswer::dispatch($question)
                    ->delay(now()->addSeconds($this->delay));

                Log::info('Dispatched answer generation job', [
                    'question_id' => $question->id,
                    'delay' => $this->delay
                ]);
            }

            // Notify admins about the new question (could be done via separate notification service)
            // $this->notifyAdmins($question);

            // Generate embeddings for the question if enabled
            if ((bool) WikiSetting::get('embedding_enabled', '1')) {
                try {
                    app(\App\Services\WikiAIService::class)->generateEmbeddingForQuestion($question);

                    Log::info('Generated embeddings for question', [
                        'question_id' => $question->id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error generating embeddings: ' . $e->getMessage(), [
                        'question_id' => $question->id
                    ]);
                    // Don't fail the entire process if embeddings fail
                }
            }
        } catch (\Exception $e) {
            Log::error('Error processing new question: ' . $e->getMessage(), [
                'question_id' => $event->question->id,
                'trace' => $e->getTraceAsString()
            ]);

            // Rethrow if we need to retry the job
            // throw $e;
        }
    }
}
