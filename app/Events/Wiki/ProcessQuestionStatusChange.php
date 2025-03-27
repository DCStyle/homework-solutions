<?php

namespace App\Listeners\Wiki;

use App\Events\Wiki\QuestionStatusChanged;
use App\Jobs\GenerateQuestionAnswer;
use App\Models\WikiSetting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ProcessQuestionStatusChange implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The time (seconds) before the job should be processed.
     *
     * @var int
     */
    public $delay = 5;

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
     * @param  QuestionStatusChanged  $event
     * @return void
     */
    public function handle(QuestionStatusChanged $event)
    {
        try {
            $question = $event->question;
            $oldStatus = $event->oldStatus;
            $newStatus = $event->newStatus;

            Log::info('Processing question status change event', [
                'question_id' => $question->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);

            // If question was approved (changed to published)
            if ($oldStatus !== 'published' && $newStatus === 'published') {
                // Check if we should automatically generate an answer
                $shouldAutoGenerate = (bool) WikiSetting::get('auto_generate_answers', '1');
                $hasAnswer = $question->answers()->where('is_ai', true)->exists();

                if ($shouldAutoGenerate && !$hasAnswer) {
                    // Dispatch job to generate answer
                    GenerateQuestionAnswer::dispatch($question)
                        ->delay(now()->addSeconds($this->delay));

                    Log::info('Dispatched answer generation job after status change', [
                        'question_id' => $question->id,
                        'delay' => $this->delay
                    ]);
                }

                // Notify the author that their question was published
                // You could implement notification logic here or via a service
            }

            // If question was rejected
            if ($newStatus === 'rejected') {
                // Notify the author that their question was rejected
                // You could implement notification logic here or via a service
            }
        } catch (\Exception $e) {
            Log::error('Error processing question status change: ' . $e->getMessage(), [
                'question_id' => $event->question->id,
                'trace' => $e->getTraceAsString()
            ]);

            // Rethrow if we need to retry the job
            throw $e;
        }
    }
}
