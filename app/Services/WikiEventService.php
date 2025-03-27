<?php

namespace App\Services;

use App\Models\WikiQuestion;
use App\Models\WikiAnswer;
use App\Models\WikiComment;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WikiEventService
{
    /**
     * Handle new question created event
     *
     * @param WikiQuestion $question
     * @return void
     */
    public function onQuestionCreated(WikiQuestion $question): void
    {
        try {
            // Get question author
            $user = $question->user;

            // Log creation event
            Log::info('New question created', [
                'question_id' => $question->id,
                'user_id' => $user->id,
                'category_id' => $question->category_id,
                'book_group_id' => $question->book_group_id
            ]);

            // Notify the user that their question was received
            $this->notifyUserQuestionReceived($user, $question);

            // Notify administrators about the new question
            $this->notifyAdminsNewQuestion($question);

            // If auto-generation is enabled, schedule answer generation
            if ($this->shouldAutoGenerateAnswer()) {
                $this->scheduleAnswerGeneration($question);
            }
        } catch (\Exception $e) {
            Log::error('Error handling question created event: ' . $e->getMessage(), [
                'question_id' => $question->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Handle question status change event
     *
     * @param WikiQuestion $question
     * @param string $oldStatus
     * @param string $newStatus
     * @return void
     */
    public function onQuestionStatusChanged(WikiQuestion $question, string $oldStatus, string $newStatus): void
    {
        try {
            // Log status change
            Log::info('Question status changed', [
                'question_id' => $question->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'user_id' => $question->user_id
            ]);

            // Handle status-specific actions
            switch ($newStatus) {
                case 'published':
                    $this->onQuestionPublished($question, $oldStatus);
                    break;

                case 'rejected':
                    $this->onQuestionRejected($question, $oldStatus);
                    break;

                case 'pending':
                    // Usually nothing to do when reverting to pending
                    break;
            }
        } catch (\Exception $e) {
            Log::error('Error handling question status change event: ' . $e->getMessage(), [
                'question_id' => $question->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Handle answer created event
     *
     * @param WikiAnswer $answer
     * @return void
     */
    public function onAnswerCreated(WikiAnswer $answer): void
    {
        try {
            // Don't process events for AI-generated answers
            if ($answer->is_ai) {
                return;
            }

            // Get related question and users
            $question = $answer->question;
            $questionAuthor = $question->user;
            $answerAuthor = $answer->user;

            // Log creation event
            Log::info('New answer created', [
                'answer_id' => $answer->id,
                'question_id' => $question->id,
                'user_id' => $answerAuthor->id,
                'is_author_answer' => $questionAuthor->id === $answerAuthor->id
            ]);

            // Notify question author about new answer (if different from answer author)
            if ($questionAuthor->id !== $answerAuthor->id) {
                $this->notifyUserNewAnswer($questionAuthor, $question, $answer);
            }
        } catch (\Exception $e) {
            Log::error('Error handling answer created event: ' . $e->getMessage(), [
                'answer_id' => $answer->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Handle comment created event
     *
     * @param WikiComment $comment
     * @return void
     */
    public function onCommentCreated(WikiComment $comment): void
    {
        try {
            // Get related question and users
            $question = $comment->question;
            $questionAuthor = $question->user;
            $commentAuthor = $comment->user;

            // Log creation event
            Log::info('New comment created', [
                'comment_id' => $comment->id,
                'question_id' => $question->id,
                'user_id' => $commentAuthor->id,
                'is_reply' => !is_null($comment->parent_id),
                'parent_id' => $comment->parent_id
            ]);

            // If this is a reply to another comment, notify that comment's author
            if ($comment->parent_id) {
                $parentComment = WikiComment::find($comment->parent_id);
                if ($parentComment && $parentComment->user_id !== $commentAuthor->id) {
                    $this->notifyUserCommentReply($parentComment->user, $question, $comment);
                }
            }
            // Otherwise, notify question author about new comment
            else if ($questionAuthor->id !== $commentAuthor->id) {
                $this->notifyUserNewComment($questionAuthor, $question, $comment);
            }
        } catch (\Exception $e) {
            Log::error('Error handling comment created event: ' . $e->getMessage(), [
                'comment_id' => $comment->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Special handler for when a question is published
     *
     * @param WikiQuestion $question
     * @param string $oldStatus
     * @return void
     */
    private function onQuestionPublished(WikiQuestion $question, string $oldStatus): void
    {
        try {
            // If question was just published, notify the author
            if ($oldStatus !== 'published') {
                $this->notifyUserQuestionPublished($question->user, $question);
            }

            // Update AI answer if needed
            $this->ensureAIAnswer($question);
        } catch (\Exception $e) {
            Log::error('Error in onQuestionPublished: ' . $e->getMessage(), [
                'question_id' => $question->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Special handler for when a question is rejected
     *
     * @param WikiQuestion $question
     * @param string $oldStatus
     * @return void
     */
    private function onQuestionRejected(WikiQuestion $question, string $oldStatus): void
    {
        try {
            // Notify the author that their question was rejected
            $this->notifyUserQuestionRejected($question->user, $question);
        } catch (\Exception $e) {
            Log::error('Error in onQuestionRejected: ' . $e->getMessage(), [
                'question_id' => $question->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Schedule AI answer generation for a question
     *
     * @param WikiQuestion $question
     * @return void
     */
    private function scheduleAnswerGeneration(WikiQuestion $question): void
    {
        try {
            // Dispatch a job to generate the answer asynchronously
            \App\Jobs\GenerateQuestionAnswer::dispatch($question);
        } catch (\Exception $e) {
            Log::error('Error scheduling answer generation: ' . $e->getMessage(), [
                'question_id' => $question->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Ensure question has an AI-generated answer
     *
     * @param WikiQuestion $question
     * @return void
     */
    private function ensureAIAnswer(WikiQuestion $question): void
    {
        try {
            // Check if question already has an AI answer
            $aiAnswer = WikiAnswer::where('question_id', $question->id)
                ->where('is_ai', true)
                ->first();

            // If no AI answer exists and auto-generation is enabled, schedule one
            if (!$aiAnswer && $this->shouldAutoGenerateAnswer()) {
                $this->scheduleAnswerGeneration($question);
            }
        } catch (\Exception $e) {
            Log::error('Error checking AI answer: ' . $e->getMessage(), [
                'question_id' => $question->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Check if automatic answer generation is enabled
     *
     * @return bool
     */
    private function shouldAutoGenerateAnswer(): bool
    {
        return (bool) \App\Models\WikiSetting::get('auto_generate_answers', '1');
    }

    /**
     * Notify user their question was received
     *
     * @param User $user
     * @param WikiQuestion $question
     * @return void
     */
    private function notifyUserQuestionReceived(User $user, WikiQuestion $question): void
    {
        // Implementation will depend on notification system
        // This is a stub for now
    }

    /**
     * Notify user their question was published
     *
     * @param User $user
     * @param WikiQuestion $question
     * @return void
     */
    private function notifyUserQuestionPublished(User $user, WikiQuestion $question): void
    {
        // Implementation will depend on notification system
        // This is a stub for now
    }

    /**
     * Notify user their question was rejected
     *
     * @param User $user
     * @param WikiQuestion $question
     * @return void
     */
    private function notifyUserQuestionRejected(User $user, WikiQuestion $question): void
    {
        // Implementation will depend on notification system
        // This is a stub for now
    }

    /**
     * Notify user about new answer to their question
     *
     * @param User $user
     * @param WikiQuestion $question
     * @param WikiAnswer $answer
     * @return void
     */
    private function notifyUserNewAnswer(User $user, WikiQuestion $question, WikiAnswer $answer): void
    {
        // Implementation will depend on notification system
        // This is a stub for now
    }

    /**
     * Notify user about new comment on their question
     *
     * @param User $user
     * @param WikiQuestion $question
     * @param WikiComment $comment
     * @return void
     */
    private function notifyUserNewComment(User $user, WikiQuestion $question, WikiComment $comment): void
    {
        // Implementation will depend on notification system
        // This is a stub for now
    }

    /**
     * Notify user about reply to their comment
     *
     * @param User $user
     * @param WikiQuestion $question
     * @param WikiComment $comment
     * @return void
     */
    private function notifyUserCommentReply(User $user, WikiQuestion $question, WikiComment $comment): void
    {
        // Implementation will depend on notification system
        // This is a stub for now
    }

    /**
     * Notify administrators about new question
     *
     * @param WikiQuestion $question
     * @return void
     */
    private function notifyAdminsNewQuestion(WikiQuestion $question): void
    {
        // Implementation will depend on notification system
        // This is a stub for now
    }
}
