<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Search\SearchService;
use App\Services\Search\Transformers\ArticleSearchTransformer;
use App\Services\Search\Transformers\BookGroupSearchTransformer;
use App\Services\Search\Transformers\BookSearchTransformer;
use App\Services\Search\Transformers\PostSearchTransformer;
use App\Services\Search\Transformers\UserSearchTransformer;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    private $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function search(Request $request)
    {
        if (!$request->ajax()) {
            abort(404);
        }

        try {
            $results = $this->searchService->search($request->input('model'), [
                'term' => $request->input('search'),
                'fields' => $request->input('fields', []),
                'title_field' => $request->input('title_field'),
                'subtitle_field' => $request->input('subtitle_field'),
                'route_name' => $request->input('route_name'),
                'limit' => (int) $request->input('limit', 10),
                'with' => $this->getRelationships($request->input('model')),
                'constraints' => $this->getConstraints($request->input('model')),
                'is_admin' => filter_var($request->input('is_admin', false), FILTER_VALIDATE_BOOLEAN)
            ]);

            return response()->json($results);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }
    }

    private function getRelationships(string $model): array
    {
        // Define model-specific relationships to eager load
        return match($model) {
            'Article' => ['category', 'tags'],
            'User' => ['role', 'profile'],
            'Book' => ['group'],
            'BookGroup' => ['category', 'books'],
            'Post' => ['chapter'],
            default => []
        };
    }

    private function getConstraints(string $model): ?callable
    {
        // Define model-specific query constraints
        return match($model) {
            default => null
        };
    }
}
