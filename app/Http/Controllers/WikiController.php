<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\BookGroup;
use App\Models\WikiQuestion;
use App\Repositories\QuestionRepository;
use App\Services\WikiSearchService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WikiController extends Controller
{
    protected WikiSearchService $searchService;
    protected QuestionRepository $questionRepository;

    public function __construct(
        WikiSearchService $searchService,
        QuestionRepository $questionRepository
    ) {
        $this->searchService = $searchService;
        $this->questionRepository = $questionRepository;
    }

    /**
     * Display the main wiki page.
     */
    public function index(): View
    {
        try {
            // Get latest and trending questions for homepage
            $latestQuestions = $this->questionRepository->getLatest(
                5,
                ['user', 'category', 'answers' => function($q) { $q->where('is_ai', true)->limit(1); }]
            );

            $trendingQuestions = $this->questionRepository->getTrending(
                5,
                ['user', 'category']
            );

            // Get all categories and book groups for sidebar
            $categories = Category::all();
            $bookGroups = BookGroup::all();

            return view('wiki.index', compact(
                'latestQuestions',
                'trendingQuestions',
                'categories',
                'bookGroups'
            ));
        } catch (\Exception $e) {
            Log::error('Error loading wiki index page: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            // Provide a graceful fallback with empty collections if needed
            return view('wiki.index', [
                'latestQuestions' => collect(),
                'trendingQuestions' => collect(),
                'categories' => Category::all(),
                'bookGroups' => BookGroup::all(),
                'error' => 'Đã xảy ra lỗi khi tải dữ liệu. Vui lòng thử lại sau.'
            ]);
        }
    }

    /**
     * Display a question.
     */
    public function show(string $categorySlug, string $questionSlug): View
    {
        try {
            // Find the question by slug
            $question = WikiQuestion::where('slug', $questionSlug)
                ->published()
                ->with([
                    'user',
                    'category',
                    'bookGroup',
                    'answers' => function($q) {
                        $q->with('user')->orderBy('is_ai', 'desc')->orderBy('created_at', 'desc');
                    },
                    'comments' => function($query) {
                        $query->with(['user', 'replies.user'])
                            ->whereNull('parent_id')
                            ->orderBy('created_at', 'desc');
                    }
                ])
                ->firstOrFail();

            // Increment view count
            $this->questionRepository->incrementViews($question);

            // Get related questions using the search service
            $relatedQuestions = $this->searchService->findRelated($question);

            // Get latest and trending questions for sidebar
            $latestQuestions = $this->questionRepository->getLatest(5, ['user', 'category']);
            $trendingQuestions = $this->questionRepository->getTrending(5, ['user', 'category']);

            // Get all categories and book groups for sidebar
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
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Câu hỏi không tồn tại hoặc đã bị xóa.');
        } catch (\Exception $e) {
            Log::error('Error showing question: ' . $e->getMessage(), [
                'category_slug' => $categorySlug,
                'question_slug' => $questionSlug,
                'trace' => $e->getTraceAsString()
            ]);

            abort(500, 'Đã xảy ra lỗi khi tải dữ liệu câu hỏi. Vui lòng thử lại sau.');
        }
    }

    /**
     * Show the search results page.
     */
    public function search(Request $request): View|RedirectResponse|JsonResponse
    {
        $query = $request->input('q', '');

        if (empty($query)) {
            return redirect()->route('wiki.index');
        }

        try {
            // Get search options from request
            $options = [
                'category_id' => $request->input('category_id'),
                'book_group_id' => $request->input('book_group_id'),
                'threshold' => 0.5, // Default threshold
                'limit' => 10,
                'page' => $request->input('page', 1),
            ];

            // Handle sorting
            $sort = $request->input('sort', 'relevance');
            if ($sort !== 'relevance') {
                $options['use_basic_search'] = true;
            }

            // Perform the search
            $questions = $this->searchService->search($query, $options);

            // For AJAX requests, return JSON response
            if ($request->ajax() || $request->input('ajax') == 1) {
                $formattedQuestions = [];

                foreach ($questions as $question) {
                    $formattedQuestions[] = [
                        'id' => $question->id,
                        'title' => $question->title,
                        'slug' => $question->slug,
                        'content' => strip_tags($question->content),
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

                return response()->json([
                    'success' => true,
                    'results' => $formattedQuestions,
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
                'sort' => $sort
            ]);
        } catch (\Exception $e) {
            Log::error('Error performing search: ' . $e->getMessage(), [
                'query' => $query,
                'options' => $options ?? [],
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax() || $request->input('ajax') == 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đã xảy ra lỗi khi tìm kiếm. Vui lòng thử lại sau.',
                    'query' => $query
                ], 500);
            }

            return view('wiki.search', [
                'query' => $query,
                'questions' => collect(),
                'categories' => Category::all(),
                'bookGroups' => BookGroup::all(),
                'error' => 'Đã xảy ra lỗi khi tìm kiếm. Vui lòng thử lại sau.'
            ]);
        }
    }
}
