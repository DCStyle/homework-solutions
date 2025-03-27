<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnswerResource;
use App\Models\WikiQuestion;
use App\Http\Requests\Wiki\StoreAnswerRequest;
use App\Repositories\AnswerRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WikiAnswerController extends Controller
{
    protected $answerRepository;

    public function __construct(AnswerRepository $answerRepository)
    {
        $this->answerRepository = $answerRepository;
        $this->middleware('auth:sanctum');
    }

    /**
     * Store a newly created answer for a specific question.
     *
     * @param StoreAnswerRequest $request
     * @param WikiQuestion $question
     * @return JsonResponse
     */
    public function store(StoreAnswerRequest $request, WikiQuestion $question): JsonResponse
    {
        try {
            // Check authentication
            $user = Auth::user();
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            // Store the answer using the repository
            $answer = $this->answerRepository->store($question, $request->validated());

            return response()->json([
                'message' => 'Answer submitted successfully.',
                'answer' => $answer // Return the newly created answer with user info
            ], 201);

        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Error submitting wiki answer: ' . $e->getMessage());
            return response()->json(['message' => 'An unexpected error occurred.'], 500);
        }
    }

    /**
     * Update an existing answer.
     *
     * @param StoreAnswerRequest $request
     * @param WikiQuestion $question
     * @param int $answerId
     * @return JsonResponse
     */
    public function update(StoreAnswerRequest $request, WikiQuestion $question, int $answerId): JsonResponse
    {
        try {
            // Find the answer
            $answer = $question->answers()->findOrFail($answerId);

            // Check if user owns this answer
            if (Auth::id() !== $answer->user_id) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }

            // Update the answer
            $answer = $this->answerRepository->update($answer, $request->validated());

            return response()->json([
                'message' => 'Answer updated successfully.',
                'answer' => $answer
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating wiki answer: ' . $e->getMessage());
            return response()->json(['message' => 'An unexpected error occurred.'], 500);
        }
    }

    /**
     * Delete an answer.
     *
     * @param WikiQuestion $question
     * @param int $answerId
     * @return JsonResponse
     */
    public function destroy(WikiQuestion $question, int $answerId): JsonResponse
    {
        try {
            // Find the answer
            $answer = $question->answers()->findOrFail($answerId);

            // Check if user owns this answer or is admin
            $user = Auth::user();
            if ($user->id !== $answer->user_id && !$user->is_admin) {
                return response()->json(['message' => 'Unauthorized.'], 403);
            }

            // Delete the answer
            $answer->delete();

            return response()->json(['message' => 'Answer deleted successfully.']);

        } catch (\Exception $e) {
            Log::error('Error deleting wiki answer: ' . $e->getMessage());
            return response()->json(['message' => 'An unexpected error occurred.'], 500);
        }
    }

    /**
     * Get more answers for a question.
     *
     * @param WikiQuestion $question
     * @return JsonResponse
     */
    public function getMoreAnswers(WikiQuestion $question): JsonResponse
    {
        try {
            // Get query parameters
            $lastId = request('last_id', 0);
            $limit = min(request('limit', 3), 10); // Limit to max 10 answers per request
            $skipAi = request('skip_ai', true);

            // Query for more answers
            $query = $question->answers()->orderBy('created_at', 'desc');
            
            // Filter by last_id if provided (for pagination)
            if ($lastId) {
                $query->where('id', '>', $lastId);
            }
            
            // Skip AI answers if requested
            if ($skipAi) {
                $query->where('is_ai', false);
            }
            
            // Fetch one more than requested to determine if there are more
            $answers = $query->with('user')->take($limit + 1)->get();
            
            // Prepare the list of answers for the response
            $answersList = [];
            $counter = 0;
            foreach ($answers as $answer) {
                // Skip if we're at the limit plus one (used only to check if there are more)
                if ($counter >= $limit) {
                    break;
                }
                
                // Skip AI answers if requested
                if ($skipAi && (is_array($answer) ? $answer['is_ai'] : $answer->is_ai)) {
                    continue;
                }
                
                // Prepare user data structure if needed
                if (is_object($answer) && $answer->user) {
                    // If $answer is an Eloquent model
                    $userData = [
                        'id' => $answer->user->id,
                        'name' => $answer->user->name
                    ];
                    
                    // Convert model to array while preserving all attributes
                    $answerData = $answer->toArray();
                    $answerData['user'] = $userData;
                    $answersList[] = $answerData;
                } else if (is_array($answer) && isset($answer['user'])) {
                    // If $answer is already an array
                    $answersList[] = $answer;
                } else if (is_array($answer)) {
                    // Array but no user data
                    $answer['user'] = null;
                    $answersList[] = $answer;
                } else {
                    // Object but no user data
                    $answerData = $answer->toArray();
                    $answerData['user'] = null;
                    $answersList[] = $answerData;
                }
                
                $counter++;
            }
            
            try {
                return response()->json([
                    'success' => true,
                    'answers' => AnswerResource::collection(collect($answersList)),
                    'has_more' => $counter > $limit,
                    'question_id' => $question->id
                ]);
            } catch (\Exception $e) {
                Log::error('Error preparing answers response: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Error loading answers'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error getting more answers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving answers.'
            ], 500);
        }
    }
}
