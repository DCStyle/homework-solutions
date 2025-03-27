<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WikiQuestion;
use App\Http\Resources\QuestionResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WikiFeedController extends Controller
{
    /**
     * Get paginated questions for the wiki feed.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getFeedQuestions(Request $request): JsonResponse
    {
        try {
            // Validate request inputs
            $validated = $request->validate([
                'limit' => 'nullable|integer|min:1|max:50',
                'page' => 'nullable|integer|min:1',
                'category_id' => 'nullable|exists:categories,id',
                'book_group_id' => 'nullable|exists:book_groups,id',
                'show_all_answers' => 'nullable|boolean'
            ]);

            $limit = $validated['limit'] ?? 10; // Number of items per page
            $answerLimit = $validated['show_all_answers'] ? null : 3; // Number of answers to show per question initially

            // Build the query
            $query = WikiQuestion::published()
                ->with([
                    'user:id,name', // Select specific columns for efficiency
                    'category:id,name,slug',
                ]);

            // Add answer relationship with limits if needed
            $query->with(['answers' => function ($answerQuery) use ($answerLimit) {
                $answerQuery->with('user:id,name') // Load user for answers
                ->orderBy('is_ai', 'desc') // AI answers first
                ->orderBy('created_at', 'desc'); // Then newest first

                if ($answerLimit) {
                    $answerQuery->limit($answerLimit);
                }
            }]);

            // Apply category filter if provided
            if (isset($validated['category_id'])) {
                $query->where('category_id', $validated['category_id']);
            }

            // Apply book group filter if provided
            if (isset($validated['book_group_id'])) {
                $query->where('book_group_id', $validated['book_group_id']);
            }

            // Order by newest first
            $query->orderBy('created_at', 'desc');

            // Paginate the results
            $questions = $query->paginate($limit);

            // Return formatted response using resources
            return response()->json([
                'success' => true,
                'questions' => QuestionResource::collection($questions),
                'meta' => [
                    'current_page' => $questions->currentPage(),
                    'per_page' => $questions->perPage(),
                    'total' => $questions->total(),
                    'last_page' => $questions->lastPage()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving feed questions: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving the feed.'
            ], 500);
        }
    }

    /**
     * Get trending questions for the feed.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getTrendingQuestions(Request $request): JsonResponse
    {
        try {
            $limit = $request->input('limit', 5);

            $questions = WikiQuestion::published()
                ->with(['user:id,name', 'category:id,name,slug'])
                ->orderBy('views', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'questions' => QuestionResource::collection($questions)
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving trending questions: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving trending questions.'
            ], 500);
        }
    }

    /**
     * Get latest questions for the feed.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getLatestQuestions(Request $request): JsonResponse
    {
        try {
            $limit = $request->input('limit', 5);

            $questions = WikiQuestion::published()
                ->with(['user:id,name', 'category:id,name,slug'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'questions' => QuestionResource::collection($questions)
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving latest questions: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving latest questions.'
            ], 500);
        }
    }
}
