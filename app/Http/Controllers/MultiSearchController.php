<?php

namespace App\Http\Controllers;

use App\Services\Search\MultiSearchService;
use Illuminate\Http\Request;

class MultiSearchController extends Controller
{
    private $multiSearchService;

    public function __construct(MultiSearchService $multiSearchService)
    {
        $this->multiSearchService = $multiSearchService;
    }

    public function search(Request $request)
    {
        if (!$request->ajax()) {
            abort(404);
        }

        try {
            $results = $this->multiSearchService->search(
                term: $request->input('search'),
                isAdmin: filter_var($request->input('is_admin', false), FILTER_VALIDATE_BOOLEAN),
                models: $request->input('models') ? explode(',', $request->input('models')) : null
            );

            return response()->json($results);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 422);
        }
    }
}
