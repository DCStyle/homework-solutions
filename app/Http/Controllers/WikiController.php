<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\BookGroup;
use App\Models\WikiQuestion;
use App\Models\WikiSetting;
use App\Services\WikiSearchService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class WikiController extends Controller
{
    protected WikiSearchService $searchService;

    public function __construct(WikiSearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Display the main wiki page.
     */
    public function index(): View
    {
        $latestQuestions = WikiQuestion::with(['user', 'category'])
            ->published()
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $trendingQuestions = WikiQuestion::with(['user', 'category'])
            ->published()
            ->orderBy('views', 'desc')
            ->take(5)
            ->get();

        $categories = Category::all();
        $bookGroups = BookGroup::all();

        return view('wiki.index', compact('latestQuestions', 'trendingQuestions', 'categories', 'bookGroups'));
    }

    /**
     * Display a question.
     */
    public function show(string $categorySlug, string $questionSlug): View
    {
        $question = WikiQuestion::where('slug', $questionSlug)
            ->published()
            ->with(['user', 'category', 'bookGroup', 'answers', 'comments' => function($query) {
                $query->with(['user', 'replies.user'])->whereNull('parent_id')->orderBy('created_at', 'desc');
            }])
            ->firstOrFail();

        // Increment view count
        $question->incrementViews();

        // Get related questions using the search service
        $relatedQuestions = $this->searchService->findRelated($question);

        $latestQuestions = WikiQuestion::with(['user', 'category'])
            ->published()
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $trendingQuestions = WikiQuestion::with(['user', 'category'])
            ->published()
            ->orderBy('views', 'desc')
            ->take(5)
            ->get();

        $categories = Category::all();
        $bookGroups = BookGroup::all();

        return view('wiki.question', [
            'question' => $question,
            'relatedQuestions' => $relatedQuestions,
            'latestQuestions' => $latestQuestions,
            'trendingQuestions' => $trendingQuestions,
            'categories' => $categories,
            'bookGroups' => $bookGroups,
        ]);
    }

    /**
     * Show the search results page.
     */
    public function search(Request $request): View|\Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $query = $request->input('q', '');

        if (empty($query)) {
            return redirect()->route('wiki.index');
        }

        // Get search options from request
        $options = [
            'category_id' => $request->input('category_id'),
            'book_group_id' => $request->input('book_group_id'),
            'threshold' => 0.5, // Default threshold
            'limit' => 10,
            'page' => $request->input('page'),
        ];

        // Handle sorting
        $sort = $request->input('sort', 'relevance');
        $questions = null;

        // Use vector search for relevance-based sorting
        if ($sort === 'relevance') {
            $questions = $this->searchService->search($query, $options);
        } else {
            // For other sorting methods, use the database query builder
            $questionsQuery = WikiQuestion::published()
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('content', 'like', "%{$query}%");
                })
                ->with('user', 'category');

            // Apply category filter if specified
            if (!empty($options['category_id'])) {
                $questionsQuery->where('category_id', $options['category_id']);
            }

            // Apply book group filter if specified
            if (!empty($options['book_group_id'])) {
                $questionsQuery->where('book_group_id', $options['book_group_id']);
            }

            // Apply sorting
            switch ($sort) {
                case 'date_asc':
                    $questionsQuery->orderBy('created_at', 'asc');
                    break;
                case 'views_desc':
                    $questionsQuery->orderBy('views', 'desc');
                    break;
                default:
                    $questionsQuery->orderBy('created_at', 'desc');
                    break;
            }

            $questions = $questionsQuery->paginate(10)->withQueryString();
        }

        // For AJAX requests, return JSON response
        if ($request->ajax() || $request->input('ajax') == 1) {
            $results = [];

            // Format the questions data for JSON response
            if ($sort === 'relevance') {
                // Handle paginated collection from vector search
                foreach ($questions->items() as $question) {
                    $results[] = $this->formatQuestionForJson($question);
                }
            } else {
                // Handle regular Laravel pagination
                foreach ($questions as $question) {
                    $results[] = $this->formatQuestionForJson($question);
                }
            }

            return response()->json([
                'success' => true,
                'results' => $results,
                'total' => $questions->total(),
                'query' => $query
            ]);
        }

        // For regular page visits, return the view
        return view('wiki.search', [
            'query' => $query,
            'questions' => $questions,
            'categories' => Category::all(),
            'bookGroups' => BookGroup::all(),
        ]);
    }

    /**
     * Format a question for JSON response.
     */
    private function formatQuestionForJson(WikiQuestion $question): array
    {
        return [
            'id' => $question->id,
            'title' => $question->title,
            'slug' => $question->slug,
            'content' => $question->content,
            'views' => $question->views,
            'category' => $question->category ? [
                'id' => $question->category->id,
                'name' => $question->category->name,
                'slug' => $question->category->slug,
            ] : null,
            'category_slug' => $question->category ? $question->category->slug : 'uncategorized',
            'user' => $question->user ? [
                'id' => $question->user->id,
                'name' => $question->user->name,
            ] : null,
            'created_at' => $question->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $question->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
