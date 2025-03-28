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

            $aiAnswer = $question->answers->where('is_ai', true)->first();

            // Sanitize AI answer content if it exists
            if (isset($aiAnswer) && $aiAnswer->content) {
                try {
                    // Create a custom cache directory in Laravel's storage
                    $cachePath = storage_path('app/htmlpurifier');
                    if (!file_exists($cachePath)) {
                        mkdir($cachePath, 0755, true);
                    }

                    $config = \HTMLPurifier_Config::createDefault();
                    // Set custom cache path
                    $config->set('Cache.SerializerPath', $cachePath);
                    // Configure allowed elements
                    $config->set('HTML.Allowed', 'p,b,i,strong,em,u,a[href|title|target],ul,ol,li,br,span[style],h1,h2,h3,h4,h5,h6,blockquote,code,pre,table,tr,td,th,thead,tbody,img[src|alt|width|height],hr,div');
                    $config->set('CSS.AllowedProperties', 'font,font-size,font-weight,font-style,text-decoration,padding-left,color,background-color,text-align,margin,margin-left,margin-right');

                    $purifier = new \HTMLPurifier($config);
                    $aiAnswer->content = $purifier->purify($aiAnswer->content);
                } catch (\Exception $e) {
                    // Fallback to disable caching if there's still an issue
                    Log::warning('HTMLPurifier cache error: ' . $e->getMessage() . '. Falling back to disabled cache.');

                    $config = \HTMLPurifier_Config::createDefault();
                    $config->set('Cache.DefinitionImpl', null); // Disable caching entirely
                    $config->set('HTML.Allowed', 'p,b,i,strong,em,u,a[href|title|target],ul,ol,li,br,span[style],h1,h2,h3,h4,h5,h6,blockquote,code,pre');

                    $purifier = new \HTMLPurifier($config);
                    $aiAnswer->content = $purifier->purify($aiAnswer->content);
                }
            }

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
                'aiAnswer' => $aiAnswer,
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
        } catch (\Exception $e) {
            Log::error('Error performing search: ' . $e->getMessage(), [
                'query' => $query,
                'options' => $options ?? [],
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi tìm kiếm. Vui lòng thử lại sau.',
                'query' => $query
            ], 500);
        }
    }
}
