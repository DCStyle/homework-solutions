<?php

namespace App\Listeners\Wiki;

use App\Events\Wiki\AnswerCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ProcessNewAnswer implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AnswerCreated $event): void
    {
        $answer = $event->answer;
        $question = $answer->question;

        try {
            // Log the answer creation
            Log::info('New answer created for question', [
                'question_id' => $question->id,
                'answer_id' => $answer->id,
                'is_ai' => $answer->is_ai,
            ]);

            // You can add additional processing here:
            // - Send notifications to the question author
            // - Update any relevant statistics
            // - Perform any additional indexing for search
            
            // Update question status if it wasn't already published
            if ($question->status !== 'published') {
                $question->status = 'published';
                $question->save();
            }

        } catch (\Exception $e) {
            Log::error('Error processing new answer event: ' . $e->getMessage(), [
                'question_id' => $question->id,
                'answer_id' => $answer->id,
                'exception' => $e,
            ]);
        }
    }
}
