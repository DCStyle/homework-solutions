<?php

namespace App\Repositories;

use App\Models\WikiComment;
use App\Models\WikiQuestion;
use App\Models\WikiSetting;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class CommentRepository
{
    /**
     * Get paginated comments for a question
     *
     * @param WikiQuestion $question
     * @param int $lastId
     * @param int $limit
     * @return Collection
     */
    public function getForQuestion(WikiQuestion $question, int $lastId = 0, int $limit = 10): Collection
    {
        return WikiComment::with(['user:id,name'])
            ->where('question_id', $question->id)
            ->where('id', '>', $lastId)
            ->whereNull('parent_id')
            ->orderBy('created_at', 'asc')
            ->limit($limit + 1) // Get one extra to check if there are more
            ->get();
    }

    /**
     * Create a new comment
     *
     * @param WikiQuestion $question
     * @param array $data
     * @return WikiComment
     */
    public function store(WikiQuestion $question, array $data): WikiComment
    {
        $comment = new WikiComment();
        $comment->question_id = $question->id;
        $comment->user_id = Auth::id();
        $comment->content = $data['content'];
        $comment->parent_id = $data['parent_id'] ?? null;
        $comment->save();

        return $comment->fresh(['user', 'replies.user']);
    }

    /**
     * Update an existing comment
     *
     * @param WikiComment $comment
     * @param array $data
     * @return WikiComment
     */
    public function update(WikiComment $comment, array $data): WikiComment
    {
        $comment->content = $data['content'];
        $comment->save();

        return $comment->fresh(['user', 'replies.user']);
    }

    /**
     * Delete a comment or mark as deleted if it has replies
     *
     * @param WikiComment $comment
     * @return bool
     */
    public function delete(WikiComment $comment): bool
    {
        // If this is a parent comment with replies, just mark as deleted
        if ($comment->replies()->count() > 0) {
            $comment->content = '[Deleted]';
            return $comment->save();
        }

        // Otherwise, delete it
        return $comment->delete();
    }

    /**
     * Check if the user should be moderated based on rate limits
     *
     * @return bool
     */
    public function shouldModerateUser(): bool
    {
        // Check if moderation is enabled
        $moderationEnabled = WikiSetting::get('moderation_enabled', '1') === '1';

        if (!$moderationEnabled) {
            return false;
        }

        // Get the max comments per day setting
        $maxCommentsPerDay = (int) WikiSetting::get('max_comments_per_day', 30);

        // If unlimited (0), no moderation
        if ($maxCommentsPerDay === 0) {
            return false;
        }

        // Count today's comments by the user
        $todayCommentCount = WikiComment::where('user_id', Auth::id())
            ->whereDate('created_at', now()->toDateString())
            ->count();

        // Check if user has reached the limit
        return $todayCommentCount >= $maxCommentsPerDay;
    }
}
