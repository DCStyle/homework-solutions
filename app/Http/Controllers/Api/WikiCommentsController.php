<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WikiComment;
use App\Models\WikiQuestion;
use App\Http\Requests\Wiki\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Repositories\CommentRepository;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WikiCommentsController extends Controller
{
    use ApiResponseTrait;

    protected $commentRepository;

    public function __construct(CommentRepository $commentRepository)
    {
        $this->middleware('auth:sanctum')->except('getForQuestion');
        $this->commentRepository = $commentRepository;
    }

    /**
     * Store a newly created comment in storage.
     *
     * @param StoreCommentRequest $request
     * @return JsonResponse
     */
    public function store(StoreCommentRequest $request): JsonResponse
    {
        try {
            $questionId = $request->input('question_id');

            // Find the question
            $question = WikiQuestion::findOrFail($questionId);

            // Create the comment using the repository
            $comment = $this->commentRepository->store($question, $request->validated());

            // Return API-formatted response
            return $this->createdResponse(
                new CommentResource($comment),
                'Comment created successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Question not found');
        } catch (\Exception $e) {
            Log::error('Error creating comment: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'question_id' => $request->input('question_id'),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse('An error occurred while creating the comment');
        }
    }

    /**
     * Update the specified comment in storage.
     *
     * @param Request $request
     * @param WikiComment $comment
     * @return JsonResponse
     */
    public function update(Request $request, WikiComment $comment): JsonResponse
    {
        try {
            // Check if user owns this comment
            if (Auth::id() !== $comment->user_id) {
                return $this->unauthorizedResponse('You are not authorized to update this comment');
            }

            // Validate the request
            $validated = $request->validate([
                'content' => 'required|string|min:5',
            ]);

            // Update the comment through the repository
            $comment = $this->commentRepository->update($comment, $validated);

            // Return API-formatted response
            return $this->successResponse(
                new CommentResource($comment),
                'Comment updated successfully'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            Log::error('Error updating comment: ' . $e->getMessage(), [
                'comment_id' => $comment->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse('An error occurred while updating the comment');
        }
    }

    /**
     * Remove the specified comment from storage.
     *
     * @param WikiComment $comment
     * @return JsonResponse
     */
    public function destroy(WikiComment $comment): JsonResponse
    {
        try {
            // Check if user owns this comment or is admin
            if (Auth::id() !== $comment->user_id && !Auth::user()->is_admin) {
                return $this->unauthorizedResponse('You are not authorized to delete this comment');
            }

            // Delete the comment
            $this->commentRepository->delete($comment);

            // Return API-formatted response
            return $this->deletedResponse('Comment deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting comment: ' . $e->getMessage(), [
                'comment_id' => $comment->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse('An error occurred while deleting the comment');
        }
    }

    /**
     * Get all comments for a question.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getForQuestion(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'question_id' => 'required|exists:wiki_questions,id',
                'last_id' => 'nullable|integer',
                'limit' => 'nullable|integer|min:1|max:50',
            ]);

            $questionId = $validated['question_id'];
            $lastId = $validated['last_id'] ?? 0;
            $limit = $validated['limit'] ?? 10;

            $question = WikiQuestion::findOrFail($questionId);

            // Get comments using repository
            $comments = $this->commentRepository->getForQuestion($question, $lastId, $limit);

            // Check if there are more comments
            $hasMore = $comments->count() > $limit;

            // Limit the result set if needed
            if ($hasMore) {
                $comments = $comments->take($limit);
            }

            // Return API-formatted response
            return $this->successResponse([
                'success' => true,
                'comments' => CommentResource::collection($comments),
                'has_more' => $hasMore,
                'question_id' => $questionId
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->notFoundResponse('Question not found');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            Log::error('Error retrieving comments: ' . $e->getMessage(), [
                'question_id' => $request->input('question_id'),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse('An error occurred while retrieving comments');
        }
    }
}
