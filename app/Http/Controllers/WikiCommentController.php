<?php

namespace App\Http\Controllers;

use App\Models\WikiComment;
use App\Models\WikiQuestion;
use App\Models\WikiSetting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class WikiCommentController extends Controller
{
    /**
     * Store a newly created comment.
     */
    public function store(Request $request, WikiQuestion $question): JsonResponse
    {
        // Check if moderation is enabled and if user has exceeded daily comment limit
        if ($this->shouldModerateUser($request)) {
            return response()->json([
                'success' => false,
                'message' => 'You have reached the maximum number of comments allowed per day.'
            ], 429);
        }
        
        try {
            // Validate the request
            $validated = $request->validate([
                'content' => 'required|string|min:5',
                'parent_id' => 'nullable|exists:wiki_comments,id',
            ]);
            
            // Create the comment
            $comment = new WikiComment();
            $comment->question_id = $question->id;
            $comment->user_id = Auth::id();
            $comment->content = $validated['content'];
            $comment->parent_id = $validated['parent_id'] ?? null;
            $comment->save();
            
            // Prepare the response data with user and nested replies
            $commentWithRelations = WikiComment::with(['user', 'replies.user'])
                ->findOrFail($comment->id);
            
            return response()->json([
                'success' => true,
                'comment' => $commentWithRelations,
                'message' => 'Comment added successfully!'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error adding comment: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'There was an error adding your comment. Please try again.'
            ], 500);
        }
    }
    
    /**
     * Update the specified comment.
     */
    public function update(Request $request, WikiComment $comment): JsonResponse
    {
        // Check if user is authorized to update the comment
        if (Auth::id() !== $comment->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }
        
        try {
            // Validate the request
            $validated = $request->validate([
                'content' => 'required|string|min:5',
            ]);
            
            // Update the comment
            $comment->content = $validated['content'];
            $comment->save();
            
            return response()->json([
                'success' => true,
                'comment' => $comment->fresh(['user', 'replies.user']),
                'message' => 'Comment updated successfully!'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating comment: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'There was an error updating your comment. Please try again.'
            ], 500);
        }
    }
    
    /**
     * Remove the specified comment.
     */
    public function destroy(WikiComment $comment): JsonResponse
    {
        // Check if user is authorized to delete the comment
        if (Auth::id() !== $comment->user_id && !Auth::user()->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }
        
        try {
            // If this is a parent comment with replies, just mark as deleted
            if ($comment->replies()->count() > 0) {
                $comment->content = '[Deleted]';
                $comment->save();
            } else {
                // Otherwise, delete it
                $comment->delete();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Comment deleted successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting comment: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'There was an error deleting your comment. Please try again.'
            ], 500);
        }
    }
    
    /**
     * Load more comments for a question.
     */
    public function loadMore(Request $request, WikiQuestion $question): JsonResponse
    {
        try {
            $lastId = $request->input('last_id', 0);
            $limit = $request->input('limit', 10);
            
            $comments = WikiComment::with(['user', 'replies.user'])
                ->where('question_id', $question->id)
                ->where('id', '>', $lastId)
                ->whereNull('parent_id')
                ->orderBy('created_at', 'asc')
                ->limit($limit)
                ->get();
            
            return response()->json([
                'success' => true,
                'comments' => $comments,
                'has_more' => $comments->count() === $limit
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading comments: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'There was an error loading comments. Please try again.'
            ], 500);
        }
    }
    
    /**
     * Check if the user's comments should be moderated based on rate limits.
     */
    private function shouldModerateUser(Request $request): bool
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