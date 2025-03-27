<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\BookGroup;
use App\Models\WikiQuestion;
use App\Repositories\QuestionRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class WikiFeedController extends Controller
{
    protected $questionRepository;

    public function __construct(QuestionRepository $questionRepository)
    {
        $this->questionRepository = $questionRepository;
    }

    /**
     * Display the main wiki feed page.
     *
     * @param Request $request
     * @return View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Get page and limit from request
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 10);
            $categoryId = $request->input('category_id');
            $bookGroupId = $request->input('book_group_id');
            $isAjaxRequest = $request->ajax() || $request->input('ajax') == 1;

            // Define relationships to eager load
            $relations = [
                'user:id,name',
                'category:id,name,slug',
                'answers' => function ($query) {
                    $query->with('user:id,name')
                        ->orderBy('is_ai', 'desc')
                        ->orderBy('created_at', 'desc');
                },
                'comments' => function($query) {
                    $query->with('user:id,name')
                        ->whereNull('parent_id')
                        ->orderBy('created_at', 'desc');
                }
            ];

            // Get feed questions with pagination
            $feedQuestions = null;

            // Filter by category if provided
            if ($categoryId) {
                $feedQuestions = $this->questionRepository->findByCategory(
                    $categoryId,
                    $limit,
                    $relations
                );
            }
            // Filter by book group if provided
            else if ($bookGroupId) {
                $feedQuestions = $this->questionRepository->findByBookGroup(
                    $bookGroupId,
                    $limit,
                    $relations
                );
            }
            // No filters, get all questions
            else {
                $feedQuestions = $this->questionRepository->getPaginated(
                    $limit,
                    $relations
                );
            }

            // For AJAX requests, return JSON with HTML fragment for infinite scrolling
            if ($isAjaxRequest) {
                $html = '';
                // Use dedicated view file for question items to avoid inconsistencies
                foreach ($feedQuestions as $question) {
                    $html .= view('wiki.partials.feed-item', ['question' => $question])->render();
                }

                return response()->json([
                    'success' => true,
                    'html' => $html,
                    'has_more' => $feedQuestions->hasMorePages(),
                    'current_page' => $feedQuestions->currentPage(),
                    'last_page' => $feedQuestions->lastPage(),
                    'total' => $feedQuestions->total(),
                    'debug' => [
                        'page' => $page,
                        'limit' => $limit,
                        'ajax' => true
                    ]
                ]);
            }

            // Get latest and trending questions for sidebar
            $latestQuestions = $this->questionRepository->getLatest(5, ['user', 'category']);
            $trendingQuestions = $this->questionRepository->getTrending(5, ['user', 'category']);

            // Get all categories and book groups for filters and sidebar
            $categories = Category::all();
            $bookGroups = BookGroup::all();

            return view('wiki.feed', compact(
                'feedQuestions',
                'latestQuestions',
                'trendingQuestions',
                'categories',
                'bookGroups'
            ));
        } catch (\Exception $e) {
            Log::error('Error loading feed page: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'page' => $page,
                'limit' => $limit
            ]);

            // Provide a graceful fallback
            return view('wiki.feed', [
                'feedQuestions' => WikiQuestion::published()
                    ->with(['user', 'category'])
                    ->orderBy('created_at', 'desc')
                    ->paginate(10),
                'latestQuestions' => collect(),
                'trendingQuestions' => collect(),
                'categories' => Category::all(),
                'bookGroups' => BookGroup::all(),
                'error' => 'Đã xảy ra lỗi khi tải dữ liệu. Vui lòng thử lại sau.'
            ]);
        }
    }

    /**
     * Display feed filtered by category.
     *
     * @param string $categorySlug
     * @param Request $request
     * @return View|\Illuminate\Http\JsonResponse
     */
    public function categoryFeed(string $categorySlug, Request $request)
    {
        try {
            // Find the category by slug
            $category = Category::where('slug', $categorySlug)->firstOrFail();

            // Get page and limit from request
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 10);
            $isAjaxRequest = $request->ajax() || $request->input('ajax') == 1;

            // Get questions for this category
            $feedQuestions = $this->questionRepository->findByCategory(
                $category->id,
                $limit,
                [
                    'user:id,name',
                    'category:id,name,slug',
                    'answers' => function ($query) {
                        $query->with('user:id,name')
                            ->orderBy('is_ai', 'desc')
                            ->orderBy('created_at', 'desc');
                    },
                    'comments' => function($query) {
                        $query->with('user:id,name')
                            ->whereNull('parent_id')
                            ->orderBy('created_at', 'desc');
                    }
                ]
            );

            // For AJAX requests, return JSON with HTML fragment for infinite scrolling
            if ($isAjaxRequest) {
                $html = '';
                // Use dedicated view file for question items to avoid inconsistencies
                foreach ($feedQuestions as $question) {
                    $html .= view('wiki.partials.feed-item', ['question' => $question])->render();
                }

                return response()->json([
                    'success' => true,
                    'html' => $html,
                    'has_more' => $feedQuestions->hasMorePages(),
                    'current_page' => $feedQuestions->currentPage(),
                    'last_page' => $feedQuestions->lastPage(),
                    'total' => $feedQuestions->total(),
                    'debug' => [
                        'page' => $page,
                        'limit' => $limit,
                        'ajax' => true
                    ]
                ]);
            }

            // Get latest and trending questions for sidebar
            $latestQuestions = $this->questionRepository->getLatest(5, ['user', 'category']);
            $trendingQuestions = $this->questionRepository->getTrending(5, ['user', 'category']);

            // Get all categories and book groups for sidebar
            $categories = Category::all();
            $bookGroups = BookGroup::all();

            return view('wiki.feed', compact(
                'feedQuestions',
                'latestQuestions',
                'trendingQuestions',
                'categories',
                'bookGroups',
                'category'
            ));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Danh mục không tồn tại');
        } catch (\Exception $e) {
            Log::error('Error loading category feed: ' . $e->getMessage(), [
                'category_slug' => $categorySlug,
                'trace' => $e->getTraceAsString()
            ]);

            abort(500, 'Đã xảy ra lỗi khi tải dữ liệu. Vui lòng thử lại sau.');
        }
    }

    /**
     * Display feed filtered by book group.
     *
     * @param string $bookGroupSlug
     * @param Request $request
     * @return View|\Illuminate\Http\JsonResponse
     */
    public function bookGroupFeed(string $categorySlug, string $bookGroupSlug, Request $request)
    {
        try {
            // Find the category by slug
            $category = Category::where('slug', $categorySlug)->firstOrFail();

            // Find the book group by slug
            $bookGroup = BookGroup::where('category_id', $category->id)
                ->where('slug', $bookGroupSlug)
                ->firstOrFail();

            // Get page and limit from request
            $page = $request->input('page', 1);
            $limit = $request->input('limit', 10);
            $isAjaxRequest = $request->ajax() || $request->input('ajax') == 1;

            // Get questions for this book group
            $feedQuestions = $this->questionRepository->findByBookGroup(
                $bookGroup->id,
                $limit,
                [
                    'user:id,name',
                    'category:id,name,slug',
                    'answers' => function ($query) {
                        $query->with('user:id,name')
                            ->orderBy('is_ai', 'desc')
                            ->orderBy('created_at', 'desc');
                    },
                    'comments' => function($query) {
                        $query->with('user:id,name')
                            ->whereNull('parent_id')
                            ->orderBy('created_at', 'desc');
                    }
                ]
            );

            // For AJAX requests, return JSON with HTML fragment for infinite scrolling
            if ($isAjaxRequest) {
                $html = '';
                // Use dedicated view file for question items to avoid inconsistencies
                foreach ($feedQuestions as $question) {
                    $html .= view('wiki.partials.feed-item', ['question' => $question])->render();
                }

                return response()->json([
                    'success' => true,
                    'html' => $html,
                    'has_more' => $feedQuestions->hasMorePages(),
                    'current_page' => $feedQuestions->currentPage(),
                    'last_page' => $feedQuestions->lastPage(),
                    'total' => $feedQuestions->total(),
                    'debug' => [
                        'page' => $page,
                        'limit' => $limit,
                        'ajax' => true
                    ]
                ]);
            }

            // Get latest and trending questions for sidebar
            $latestQuestions = $this->questionRepository->getLatest(5, ['user', 'category']);
            $trendingQuestions = $this->questionRepository->getTrending(5, ['user', 'category']);

            // Get all categories and book groups for sidebar
            $categories = Category::all();
            $bookGroups = BookGroup::all();

            return view('wiki.feed', compact(
                'feedQuestions',
                'latestQuestions',
                'trendingQuestions',
                'categories',
                'bookGroups',
                'bookGroup'
            ));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Bộ sách không tồn tại');
        } catch (\Exception $e) {
            Log::error('Error loading book group feed: ' . $e->getMessage(), [
                'book_group_slug' => $bookGroupSlug,
                'trace' => $e->getTraceAsString()
            ]);

            abort(500, 'Đã xảy ra lỗi khi tải dữ liệu. Vui lòng thử lại sau.');
        }
    }
}
