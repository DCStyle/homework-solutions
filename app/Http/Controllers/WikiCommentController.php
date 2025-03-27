<?php

namespace App\Http\Controllers;

use App\Models\WikiComment;
use App\Models\WikiQuestion;
use App\Repositories\CommentRepository;
use App\Http\Requests\Wiki\StoreCommentRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class WikiCommentController extends Controller
{
    protected $commentRepository;

    public function __construct(CommentRepository $commentRepository)
    {
        $this->commentRepository = $commentRepository;
        $this->middleware('auth')->except(['loadMoreAjax', 'loadMore']);
    }

    /**
     * Store a newly created comment.
     */
    public function store(StoreCommentRequest $request, WikiQuestion $question): JsonResponse
    {
        try {
            // Create the comment using the repository
            $comment = $this->commentRepository->store($question, $request->validated());

            return response()->json([
                'success' => true,
                'comment' => $comment,
                'message' => 'Comment added successfully!'
            ]);
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

            // Update the comment through the repository
            $comment = $this->commentRepository->update($comment, $validated);

            return response()->json([
                'success' => true,
                'comment' => $comment,
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
            // Delete comment through repository
            $this->commentRepository->delete($comment);

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

            $comments = $this->commentRepository->getForQuestion($question, $lastId, $limit);

            $hasMore = $comments->count() > $limit;

            if ($hasMore) {
                $comments = $comments->take($limit);
            }

            return response()->json([
                'success' => true,
                'comments' => $comments,
                'has_more' => $hasMore
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
     * Load more comments for a question (AJAX).
     */
    public function loadMoreAjax(Request $request, WikiQuestion $question): JsonResponse
    {
        return $this->loadMore($request, $question);
    }
}
