<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WikiQuestion;
use App\Repositories\AnswerRepository;
use App\Services\WikiSearchService;
use App\Http\Resources\QuestionResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WikiQuestionsController extends Controller
{
    protected WikiSearchService $searchService;
    protected AnswerRepository $answerRepository;

    public function __construct(WikiSearchService $searchService, AnswerRepository $answerRepository)
    {
        $this->searchService = $searchService;
        $this->answerRepository = $answerRepository;
    }

    /**
     * Search for questions.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'q' => 'required|string|min:2',
                'category_id' => 'nullable|exists:categories,id',
                'book_group_id' => 'nullable|exists:book_groups,id',
                'threshold' => 'nullable|numeric|min:0|max:1',
                'limit' => 'nullable|integer|min:1|max:50',
                'use_basic_search' => 'nullable|boolean',
            ]);

            $query = $validated['q'];

            // Remove the query from options and pass the rest
            unset($validated['q']);
            $options = $validated;

            $results = $this->searchService->search($query, $options);

            // Use the resource collection for consistent formatting
            return response()->json([
                'success' => true,
                'results' => QuestionResource::collection($results),
                'query' => $query,
                'total' => $results->total(),
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'per_page' => $results->perPage(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error in question search: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'query' => $request->input('q')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while searching. Please try again.'
            ], 500);
        }
    }

    /**
     * Show a specific question with answers and comments.
     *
     * @param WikiQuestion $question
     * @return JsonResponse
     */
    public function show(WikiQuestion $question): JsonResponse
    {
        try {
            // Load relationships for the question
            $question->load([
                'user:id,name',
                'category:id,name,slug',
                'bookGroup:id,name,slug',
                'answers' => function ($query) {
                    $query->with('user:id,name')->orderBy('is_ai', 'desc')->orderBy('created_at', 'desc');
                },
                'comments' => function($query) {
                    $query->with(['user:id,name', 'replies.user:id,name'])
                        ->whereNull('parent_id')
                        ->orderBy('created_at', 'desc');
                }
            ]);

            // Increment view count (consider moving to an event)
            $question->incrementViews();

            // Get related questions
            $relatedQuestions = $this->searchService->findRelated($question);

            return response()->json([
                'success' => true,
                'question' => new QuestionResource($question),
                'related_questions' => QuestionResource::collection($relatedQuestions),
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching question details: ' . $e->getMessage(), [
                'question_id' => $question->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving the question details.'
            ], 500);
        }
    }

    /**
     * Check if an answer exists for a question.
     *
     * @param WikiQuestion $question
     * @return JsonResponse
     */
    public function checkAnswer(WikiQuestion $question): JsonResponse
    {
        try {
            $status = $this->answerRepository->checkAnswerStatus($question);
            return response()->json($status);
        } catch (\Exception $e) {
            Log::error('Error checking answer status: ' . $e->getMessage(), [
                'question_id' => $question->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while checking the answer status.',
                'question_id' => $question->id,
                'question_status' => $question->status
            ], 500);
        }
    }
}
