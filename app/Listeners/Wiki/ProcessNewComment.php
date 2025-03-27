<?php

namespace App\Listeners\Wiki;

use App\Events\Wiki\CommentCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ProcessNewComment implements ShouldQueue
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
    public function handle(CommentCreated $event): void
    {
        $comment = $event->comment;
        $question = $comment->question;

        try {
            // Log the comment creation
            Log::info('New comment created for question', [
                'question_id' => $question->id,
                'comment_id' => $comment->id,
                'is_reply' => $comment->parent_id ? true : false,
            ]);

            // Additional processing:
            // 1. You could notify the question author about new comments
            // 2. If this is a reply to another comment, notify that comment's author
            // 3. Update any metrics or analytics about user engagement
            
            // Example of notifying the question author (pseudocode)
            // if ($comment->parent_id === null && $question->user_id !== $comment->user_id) {
            //    Notification::send($question->user, new NewCommentNotification($question, $comment));
            // }
            
            // Example of notifying the parent comment author if this is a reply (pseudocode)
            // if ($comment->parent_id !== null) {
            //    $parentComment = WikiComment::find($comment->parent_id);
            //    if ($parentComment && $parentComment->user_id !== $comment->user_id) {
            //        Notification::send($parentComment->user, new NewReplyNotification($question, $comment));
            //    }
            // }
            
        } catch (\Exception $e) {
            Log::error('Error processing new comment event: ' . $e->getMessage(), [
                'question_id' => $question->id,
                'comment_id' => $comment->id,
                'exception' => $e,
            ]);
        }
    }
}
