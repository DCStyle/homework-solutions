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
     * @param  CommentCreated  $event
     * @return void
     */
    public function handle(CommentCreated $event)
    {
        try {
            $comment = $event->comment;
            $question = $comment->question;

            Log::info('Processing new comment event', [
                'comment_id' => $comment->id,
                'question_id' => $question->id,
                'user_id' => $comment->user_id,
                'is_reply' => !is_null($comment->parent_id)
            ]);

            // If this is a reply to another comment, notify the original comment author
            if ($comment->parent_id) {
                $parentComment = \App\Models\WikiComment::find($comment->parent_id);

                if ($parentComment && $parentComment->user_id !== $comment->user_id) {
                    // Here you would implement notification logic
                    // For example:
                    // \Notification::send($parentComment->user, new CommentReplyNotification($comment));

                    Log::info('Notification should be sent to parent comment author', [
                        'parent_comment_id' => $parentComment->id,
                        'parent_author_id' => $parentComment->user_id,
                        'reply_id' => $comment->id,
                        'reply_author_id' => $comment->user_id
                    ]);
                }
            }
            // If this is a top-level comment, notify the question author
            else if ($question->user_id !== $comment->user_id) {
                // Here you would implement notification logic
                // For example:
                // \Notification::send($question->user, new NewCommentNotification($question, $comment));

                Log::info('Notification should be sent to question author', [
                    'question_id' => $question->id,
                    'question_author_id' => $question->user_id,
                    'comment_id' => $comment->id,
                    'comment_author_id' => $comment->user_id
                ]);
            }

            // Check for harmful content and potentially flag for moderation
            $this->checkContentForModeration($comment);

        } catch (\Exception $e) {
            Log::error('Error processing new comment: ' . $e->getMessage(), [
                'comment_id' => $event->comment->id,
                'trace' => $e->getTraceAsString()
            ]);

            // Don't rethrow the exception to prevent the comment from being lost
            // Just log it and continue
        }
    }

    /**
     * Check comment content for potential moderation needs.
     *
     * @param \App\Models\WikiComment $comment
     * @return void
     */
    private function checkContentForModeration($comment)
    {
        // Here you could implement content moderation logic
        // For example, check for forbidden words or phrases
        // And flag the comment for moderation if needed

        // This is just a placeholder implementation
        $content = strip_tags($comment->content);
        $forbiddenWords = ['spam', 'abuse', 'explicit']; // Example list

        foreach ($forbiddenWords as $word) {
            if (stripos($content, $word) !== false) {
                Log::warning('Comment contains potential forbidden content', [
                    'comment_id' => $comment->id,
                    'user_id' => $comment->user_id,
                    'flagged_word' => $word
                ]);

                // Here you could update a flag in the database or create a moderation record
                // $comment->requires_moderation = true;
                // $comment->save();

                break;
            }
        }
    }
}
