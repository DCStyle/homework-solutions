<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WikiQuestion;
use App\Services\WikiSearchService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WikiQuestionsController extends Controller
{
    protected WikiSearchService $searchService;

    public function __construct(WikiSearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Search for questions.
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'required|string|min:3',
            'category_id' => 'nullable|exists:categories,id',
            'book_group_id' => 'nullable|exists:book_groups,id',
            'threshold' => 'nullable|numeric|min:0|max:1',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $query = $validated['q'];
        unset($validated['q']);

        $results = $this->searchService->search($query, $validated);

        return response()->json([
            'data' => $results,
            'query' => $query,
            'total' => $results->count(),
        ]);
    }
}