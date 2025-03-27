<?php

namespace App\Listeners\Wiki;

use App\Events\Wiki\QuestionStatusChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ProcessQuestionStatusChange implements ShouldQueue
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
    public function handle(QuestionStatusChanged $event): void
    {
        $question = $event->question;
        $oldStatus = $event->oldStatus;
        $newStatus = $event->newStatus;

        try {
            // Log the status change
            Log::info('Question status changed', [
                'question_id' => $question->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);

            // You can implement different logic based on the status transition
            if ($oldStatus !== 'published' && $newStatus === 'published') {
                // Question was just published
                
                // Add any notification logic here
                // For example, notify the author that their question is now live
                
                // You could potentially also notify any followers or subscribers
                // about the new published question
            }
            
            // You could handle other status transitions here like:
            // - When a question is rejected
            // - When a question is flagged
            // - When a question is archived
                
        } catch (\Exception $e) {
            Log::error('Error processing question status change: ' . $e->getMessage(), [
                'question_id' => $question->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'exception' => $e,
            ]);
        }
    }
}
