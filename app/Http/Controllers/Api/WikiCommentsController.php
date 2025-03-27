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
use App\Http\Controllers\WikiCommentController;

class WikiCommentsController extends Controller
{
    use ApiResponseTrait;

    protected $commentRepository;
    protected $webCommentController;

    public function __construct(CommentRepository $commentRepository, WikiCommentController $webCommentController)
    {
        $this->middleware('auth:sanctum');
        $this->commentRepository = $commentRepository;
        $this->webCommentController = $webCommentController;
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
            
            // Delegate to web controller's store method
            $response = $this->webCommentController->store($request, $question);
            $responseData = json_decode($response->getContent(), true);
            
            if (!$responseData['success']) {
                throw new \Exception($responseData['message']);
            }
            
            // Return API-formatted response
            return $this->createdResponse(
                new CommentResource($responseData['comment']),
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
            // Delegate to web controller's update method
            $response = $this->webCommentController->update($request, $comment);
            $responseData = json_decode($response->getContent(), true);
            
            if (!$responseData['success']) {
                throw new \Exception($responseData['message']);
            }
            
            // Return API-formatted response
            return $this->successResponse(
                new CommentResource($responseData['comment']),
                'Comment updated successfully'
            );
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return $this->unauthorizedResponse('You are not authorized to update this comment');
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
            // Delegate to web controller's destroy method
            $response = $this->webCommentController->destroy($comment);
            $responseData = json_decode($response->getContent(), true);
            
            if (!$responseData['success']) {
                throw new \Exception($responseData['message']);
            }
            
            // Return API-formatted response
            return $this->deletedResponse('Comment deleted successfully');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return $this->unauthorizedResponse('You are not authorized to delete this comment');
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
            $question = WikiQuestion::findOrFail($questionId);
            
            // Create a new request with the required parameters
            $newRequest = new Request([
                'last_id' => $validated['last_id'] ?? 0,
                'limit' => $validated['limit'] ?? 10,
                'ajax' => 1
            ]);
            
            // Delegate to web controller's loadMore method
            $response = $this->webCommentController->loadMore($newRequest, $question);
            $responseData = json_decode($response->getContent(), true);
            
            // Check if the response has a valid structure
            if (!$responseData['success']) {
                throw new \Exception($responseData['message'] ?? 'Error loading comments');
            }
            
            // Make sure comments are properly structured
            $comments = collect($responseData['comments'])->map(function($comment) {
                // Ensure each comment has a 'user' field with at least id and name
                if (!isset($comment['user']) && isset($comment['user_id'])) {
                    $comment['user'] = [
                        'id' => $comment['user_id'],
                        'name' => $comment['user_name'] ?? 'User'
                    ];
                }
                return $comment;
            });
            
            // Return API-formatted response
            return $this->successResponse([
                'success' => true,
                'comments' => CommentResource::collection($comments),
                'has_more' => $responseData['has_more'],
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
