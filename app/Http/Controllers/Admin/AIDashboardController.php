<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookChapter;
use App\Models\BookGroup;
use App\Models\Category;
use App\Models\Post;
use App\Models\Prompt;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AIDashboardController extends Controller
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Display the AI dashboard
     */
    public function index()
    {
        // Get counts for missing SEO data
        $missingData = $this->getMissingSEOData();

        // Get custom prompts
        $prompts = Prompt::active()->orderBy('created_at', 'desc')->take(5)->get();

        // Get SEO progress stats
        $seoProgress = $this->getSEOProgress();

        return view('admin.ai-dashboard.index', compact('missingData', 'prompts', 'seoProgress'));
    }

    /**
     * Get detailed stats about SEO issues
     */
    public function stats(Request $request)
    {
        $type = $request->input('type', 'posts');
        $search = $request->input('search');
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);

        $query = $this->getQueryForType($type);

        // Apply search if provided
        if ($search) {
            $this->applySearchToQuery($query, $type, $search);
        }

        // Get results with pagination
        $items = $query->paginate($limit);

        // Get all custom prompts for this content type
        $prompts = Prompt::active()->forContentType($type)->get();

        return view('admin.ai-dashboard.stats', compact('items', 'type', 'prompts', 'search'));
    }

    // Add this new method to the AIDashboardController.php class

    /**
     * Fetch content data with hierarchical filtering for stats page
     */
    public function getStatsData(Request $request)
    {
        $type = $request->input('type', 'posts');
        $search = $request->input('search');
        $seoStatus = $request->input('seo_status', 'missing');
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 50);

        // Get hierarchical filters
        $categoryId = $request->input('category_id');
        $groupId = $request->input('group_id');
        $bookId = $request->input('book_id');
        $chapterId = $request->input('chapter_id');

        // Start with the base query for the content type
        $query = $this->getQueryForType($type);

        // Apply SEO status filter
        if ($seoStatus === 'missing') {
            // Use the existing query which already filters for missing SEO data
        } elseif ($seoStatus === 'complete') {
            // Invert the condition to find items WITH SEO data
            $query = $this->getQueryForComplete($type);
        } else {
            // 'all' status - remove the SEO filters but keep the relations
            $query = $this->getQueryForAll($type);
        }

        // Apply hierarchical filters if provided
        if ($categoryId) {
            $query = $this->applyCategoryFilter($query, $type, $categoryId);
        }

        if ($groupId) {
            $query = $this->applyGroupFilter($query, $type, $groupId);
        }

        if ($bookId) {
            $query = $this->applyBookFilter($query, $type, $bookId);
        }

        if ($chapterId) {
            $query = $this->applyChapterFilter($query, $type, $chapterId);
        }

        // Apply search if provided
        if ($search) {
            $this->applySearchToQuery($query, $type, $search);
        }

        // Get paginated results
        $items = $query->paginate($limit);

        return response()->json($items);
    }

    /**
     * Get query for items WITH SEO data
     */
    private function getQueryForComplete($type)
    {
        switch ($type) {
            case 'posts':
                return Post::whereNotNull('meta_title')
                    ->whereNotNull('meta_description')
                    ->where('meta_title', '!=', '')
                    ->where('meta_description', '!=', '')
                    ->with(['chapter.book.group.category']);

            case 'chapters':
                return BookChapter::whereNotNull('description')
                    ->where('description', '!=', '')
                    ->with(['book.group.category']);

            case 'books':
                return Book::whereNotNull('description')
                    ->where('description', '!=', '')
                    ->with(['group.category']);

            case 'book_groups':
                return BookGroup::whereNotNull('description')
                    ->where('description', '!=', '')
                    ->with(['category']);

            default:
                return Post::query(); // Fallback
        }
    }

    /**
     * Get query for ALL items (no SEO filter)
     */
    private function getQueryForAll($type)
    {
        switch ($type) {
            case 'posts':
                return Post::with(['chapter.book.group.category']);

            case 'chapters':
                return BookChapter::with(['book.group.category']);

            case 'books':
                return Book::with(['group.category']);

            case 'book_groups':
                return BookGroup::with(['category']);

            default:
                return Post::query(); // Fallback
        }
    }

    /**
     * Apply category filter to query
     */
    private function applyCategoryFilter($query, $type, $categoryId)
    {
        switch ($type) {
            case 'posts':
                return $query->whereHas('chapter.book.group', function($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                });

            case 'chapters':
                return $query->whereHas('book.group', function($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                });

            case 'books':
                return $query->whereHas('group', function($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                });

            case 'book_groups':
                return $query->where('category_id', $categoryId);

            default:
                return $query;
        }
    }

    /**
     * Apply book group filter to query
     */
    private function applyGroupFilter($query, $type, $groupId)
    {
        switch ($type) {
            case 'posts':
                return $query->whereHas('chapter.book', function($q) use ($groupId) {
                    $q->where('book_group_id', $groupId);
                });

            case 'chapters':
                return $query->whereHas('book', function($q) use ($groupId) {
                    $q->where('book_group_id', $groupId);
                });

            case 'books':
                return $query->where('book_group_id', $groupId);

            case 'book_groups':
                return $query->where('id', $groupId);

            default:
                return $query;
        }
    }

    /**
     * Apply book filter to query
     */
    private function applyBookFilter($query, $type, $bookId)
    {
        switch ($type) {
            case 'posts':
                return $query->whereHas('chapter', function($q) use ($bookId) {
                    $q->where('book_id', $bookId);
                });

            case 'chapters':
                return $query->where('book_id', $bookId);

            case 'books':
                return $query->where('id', $bookId);

            default:
                return $query;
        }
    }

    /**
     * Apply chapter filter to query
     */
    private function applyChapterFilter($query, $type, $chapterId)
    {
        switch ($type) {
            case 'posts':
                return $query->where('book_chapter_id', $chapterId);

            case 'chapters':
                return $query->where('id', $chapterId);

            default:
                return $query;
        }
    }

    /**
     * Show the playground for testing AI prompts
     */
    public function playground(Request $request)
    {
        // Get preset content ID and type if provided
        $presetType = $request->input('content_type');
        $presetId = $request->input('content_id');
        $promptId = $request->input('prompt_id');

        // Get preset content if requested
        $presetContent = null;
        if ($presetType && $presetId) {
            $presetContent = $this->getContentObject($presetType, $presetId);
        }

        // Get default and custom prompts
        $defaultPrompts = $this->getDefaultPrompt();
        $customPrompts = Prompt::active()->get();

        // Get selected prompt if requested
        $selectedPrompt = null;
        if ($promptId) {
            $selectedPrompt = Prompt::find($promptId);
        }

        return view('admin.ai-dashboard.playground', compact(
            'defaultPrompts', 'customPrompts', 'presetContent',
            'presetType', 'selectedPrompt'
        ));
    }

    /**
     * Get categories for hierarchical selection
     */
    public function getCategories()
    {
        $categories = Category::orderBy('name')->get(['id', 'name']);
        return response()->json($categories);
    }

    /**
     * Get book groups for a specific category
     */
    public function getBookGroups($categoryId)
    {
        $groups = BookGroup::where('category_id', $categoryId)
            ->get(['id', 'name', 'category_id']);
        return response()->json($groups);
    }

    /**
     * Get books for a specific group
     */
    public function getBooks($groupId)
    {
        $books = Book::where('book_group_id', $groupId)
            ->orderBy('name')
            ->get(['id', 'name', 'book_group_id']);
        return response()->json($books);
    }

    /**
     * Get chapters for a specific book
     */
    public function getChapters($bookId)
    {
        $chapters = BookChapter::where('book_id', $bookId)
            ->orderBy('name')
            ->get(['id', 'name', 'book_id']);
        return response()->json($chapters);
    }

    /**
     * Get posts for a specific chapter
     */
    public function getPosts($chapterId)
    {
        $posts = Post::where('book_chapter_id', $chapterId)
            ->orderBy('title')
            ->get(['id', 'title', 'book_chapter_id', 'meta_title', 'meta_description']);
        return response()->json($posts);
    }

    /**
     * Get detailed content information with hierarchical path
     * This is specifically for retrieving a content item with its full path
     * when direct URL navigation is used
     */
    public function getContentDetails($type, $id)
    {
        $content = $this->getContentObject($type, $id);

        if (!$content) {
            return response()->json(['error' => 'Content not found'], 404);
        }

        // For each content type, we need to make sure all relationship data is loaded
        switch ($type) {
            case 'posts':
                // For posts, load the full path: chapter -> book -> group -> category
                if ($content->chapter) {
                    $content->load([
                        'chapter.book.group.category'
                    ]);
                }
                break;

            case 'chapters':
                // For chapters, load: book -> group -> category
                $content->load([
                    'book.group.category'
                ]);
                break;

            case 'books':
                // For books, load: group -> category
                $content->load([
                    'group.category'
                ]);
                break;

            case 'book_groups':
                // For book groups, load: category
                $content->load([
                    'category'
                ]);
                break;
        }

        return response()->json($content);
    }

    /**
     * Generate a sample result using the selected model and prompt
     */
    public function generateSample(Request $request)
    {
        $contentType = $request->input('content_type');
        $contentId = $request->input('content_id');
        $model = $request->input('model', 'grok-2');
        $promptText = $request->input('prompt');
        $promptId = $request->input('prompt_id');
        $useHtmlMeta = (bool)$request->input('use_html_meta', false);

        try {
            // Get content object
            $content = $this->getContentObject($contentType, $contentId);
            if (!$content) {
                return response()->json(['error' => 'Content not found'], 404);
            }

            // Use prompt from database if ID provided
            if ($promptId) {
                $promptObj = Prompt::find($promptId);
                if ($promptObj) {
                    $promptText = $promptObj->prompt_text;
                }
            }

            // Prepare data for AI model
            $prompt = $this->preparePrompt($promptText, $content, $contentType);

            // Prepare options based on model and request parameters
            $options = [
                'content_type' => $contentType,
                'max_tokens' => (int)$request->input('max_tokens', 1000),
                'temperature' => (float)$request->input('temperature', 0.7),
                'use_html_meta' => $useHtmlMeta
            ];

            // Add model-specific parameters
            if (strpos($model, 'deepseek') === 0) {
                // Use custom system message if provided, otherwise use default
                $systemMessage = $request->input('system_message');
                if (empty($systemMessage)) {
                    $systemMessage = $this->getSystemMessage($contentType);
                }

                $options['system_message'] = $systemMessage;
                $options['model_variant'] = $request->input('deepseek_model', 'deepseek-chat');
            }

            // Call AI model to generate content
            $result = $this->aiService->generate($model, $prompt, $options);

            return response()->json([
                'success' => true,
                'result' => $result,
                'content' => $content,
                'prompt' => $prompt
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating sample', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error generating content: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a prompt
     */
    public function getPrompt(Request $request, $id)
    {
        $prompt = Prompt::findOrFail($id);

        return response()->json([
            'success' => true,
            'prompt' => $prompt
        ]);
    }

    /**
     * Get prompts by content type
     */
    public function getPromptsByType(Request $request)
    {
        $contentType = $request->input('content_type');

        $prompts = Prompt::active()
            ->when($contentType, function($query) use ($contentType) {
                return $query->where('content_type', $contentType);
            })
            ->orderBy('created_at', 'desc')
            ->get(['id', 'name', 'prompt_text', 'system_message', 'ai_model', 'content_type']);

        return response()->json($prompts);
    }

    /**
     * Apply a prompt to multiple content items
     */
    public function applyPrompt(Request $request)
    {
        try {
            $contentType = $request->input('content_type');
            $filterType = $request->input('filter_type');
            $filterId = $request->input('filter_id');
            $model = $request->input('model', 'grok-2');
            $promptText = $request->input('prompt');
            $promptId = $request->input('prompt_id');
            $useHtmlMeta = (bool)$request->input('use_html_meta', false);

            // Use prompt from database if ID provided
            if ($promptId) {
                $promptObj = Prompt::find($promptId);
                if ($promptObj) {
                    $promptText = $promptObj->prompt_text;
                }
            }

            // Get content items based on filter
            $items = $this->getContentByFilter($contentType, $filterType, $filterId);

            if (count($items) === 0) {
                return response()->json([
                    'success' => false,
                    'error' => 'No content items found matching the filter criteria'
                ], 404);
            }

            $processed = 0;
            $failed = 0;
            $errors = [];

            // Process each item
            foreach ($items as $item) {
                try {
                    $prompt = $this->preparePrompt($promptText, $item, $contentType);

                    // Prepare options based on model
                    $options = [
                        'content_type' => $contentType,
                        'max_tokens' => (int)$request->input('max_tokens', 1000),
                        'temperature' => (float)$request->input('temperature', 0.7),
                        'use_html_meta' => $useHtmlMeta
                    ];

                    // Add system message for DeepSeek
                    if (strpos($model, 'deepseek') === 0) {
                        $systemMessage = $request->input('system_message');
                        if (empty($systemMessage)) {
                            $systemMessage = $this->getSystemMessage($contentType);
                        }

                        $options['system_message'] = $systemMessage;
                        $options['model_variant'] = $request->input('deepseek_model', 'deepseek-chat');
                    }

                    $result = $this->aiService->generate($model, $prompt, $options);

                    if ($result && $this->updateContentSEO($item, $contentType, $result)) {
                        $processed++;
                    } else {
                        $failed++;
                        $errors[] = "Failed to update item ID: {$item->id}";
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "Error processing item ID {$item->id}: {$e->getMessage()}";
                    Log::error('Error processing item in bulk generation', [
                        'item_id' => $item->id,
                        'content_type' => $contentType,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'processed' => $processed,
                'failed' => $failed,
                'total' => count($items),
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            Log::error('Error in bulk apply prompt', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error processing request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save a custom prompt
     */
    public function savePrompt(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'prompt_text' => 'required|string',
                'content_type' => 'required|string|in:posts,chapters,books,book_groups',
                'description' => 'nullable|string',
                'system_message' => 'nullable|string',
                'ai_model' => 'nullable|string'
            ]);

            $prompt = new Prompt();
            $prompt->name = $request->input('name');
            $prompt->prompt_text = $request->input('prompt_text');
            $prompt->content_type = $request->input('content_type');
            $prompt->description = $request->input('description');
            $prompt->system_message = $request->input('system_message');
            $prompt->ai_model = $request->input('ai_model');
            $prompt->is_active = true;
            $prompt->created_by_user_id = Auth::id();
            $prompt->save();

            return response()->json([
                'success' => true,
                'prompt' => $prompt
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving prompt', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error saving prompt: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a prompt
     */
    public function deletePrompt(Request $request, $id)
    {
        try {
            $prompt = Prompt::findOrFail($id);
            $prompt->delete();

            return response()->json([
                'success' => true
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting prompt', [
                'error' => $e->getMessage(),
                'prompt_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Error deleting prompt: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the default optimized prompt
     */
    public function getDefaultPrompt()
    {
        return [
            'posts' => "Bạn là chuyên gia SEO cho nội dung giáo dục. Tạo tiêu đề meta và mô tả meta được tối ưu hóa SEO cho một bài đăng giáo dục với thông tin sau:\n\nTiêu đề bài đăng: {{title}}\nChương: {{chapter_name}}\nSách: {{book_name}}\nMôn học: {{group_name}}\nDanh mục: {{category_name}}\n\nPhản hồi của bạn nên bao gồm:\n1. Tiêu đề Meta (tối đa 60 ký tự)\n2. Mô tả Meta (khoảng 150-160 ký tự)\n\nĐảm bảo tiêu đề hấp dẫn, bao gồm các từ khóa liên quan đến nội dung giáo dục, hướng dẫn học tập và tài nguyên học tập. Mô tả meta nên nhấn mạnh giá trị giáo dục và bao gồm các từ hành động.",
            'chapters' => "Bạn là chuyên gia SEO cho nội dung giáo dục. Tạo mô tả hấp dẫn và được tối ưu hóa SEO cho một chương sách với thông tin sau:\n\nChương: {{name}}\nSách: {{book_name}}\nMôn học: {{group_name}}\nDanh mục: {{category_name}}\n\nTạo mô tả ngắn gọn, cung cấp thông tin (2-3 đoạn) giải thích những gì học sinh sẽ học từ chương này. Tập trung vào giá trị giáo dục, nhấn mạnh các khái niệm chính được đề cập và bao gồm các từ khóa liên quan đến giáo dục và học tập.",
            'books' => "Bạn là chuyên gia SEO cho nội dung giáo dục. Tạo mô tả được tối ưu hóa SEO cho một cuốn sách với thông tin sau:\n\nTên sách: {{name}}\nMôn học: {{group_name}}\nDanh mục: {{category_name}}\n\nViết mô tả toàn diện (3-4 đoạn) giải thích giá trị giáo dục của cuốn sách này. Bao gồm thông tin về đối tượng mục tiêu (học sinh), kết quả học tập chính và lý do tại sao cuốn sách này có giá trị cho giáo dục. Sử dụng các từ khóa giáo dục liên quan và duy trì giọng điệu chuyên nghiệp, cung cấp thông tin.",
            'book_groups' => "Bạn là chuyên gia SEO cho nội dung giáo dục. Tạo mô tả được tối ưu hóa SEO cho một môn học/khóa học với thông tin sau:\n\nMôn học: {{name}}\nDanh mục: {{category_name}}\n\nTạo mô tả hấp dẫn (2-3 đoạn) giải thích những gì học sinh sẽ học trong môn học này. Tập trung vào lợi ích giáo dục, kỹ năng phát triển và kiến thức thu được. Bao gồm các từ khóa liên quan đến giáo dục, kết quả học tập và thành tích học tập. Giọng điệu nên chuyên nghiệp và khuyến khích học sinh."
        ];
    }

    /**
     * Get system message based on content type
     */
    public function getSystemMessage($type = null)
    {
        return match ($type) {
            'posts' => "Bạn là chuyên gia SEO giáo dục. Nhiệm vụ của bạn là tạo tiêu đề meta và mô tả được tối ưu hóa SEO cho nội dung giáo dục. Tập trung vào sự rõ ràng, từ khóa liên quan và sự hấp dẫn.",
            'chapters' => "Bạn là người viết nội dung giáo dục. Nhiệm vụ của bạn là tạo mô tả rõ ràng, cung cấp thông tin cho các chương sách nhấn mạnh giá trị giáo dục và các khái niệm chính được đề cập.",
            'books' => "Bạn là chuyên gia nội dung giáo dục. Nhiệm vụ của bạn là tạo mô tả toàn diện cho sách giáo dục giải thích giá trị của chúng, đối tượng mục tiêu và kết quả học tập.",
            'book_groups' => "Bạn là chuyên gia chương trình giảng dạy. Nhiệm vụ của bạn là tạo mô tả cho các lĩnh vực môn học giải thích những gì học sinh sẽ học và các kỹ năng họ sẽ phát triển.",
            default => "Bạn là chuyên gia nội dung giáo dục có nhiệm vụ tạo nội dung chất lượng cao, được tối ưu hóa SEO cho nền tảng giáo dục."
        };
    }

    /**
     * Get content object by type and ID
     */
    private function getContentObject($type, $id)
    {
        return match ($type) {
            'posts' => Post::with(['chapter.book.group.category'])->find($id),
            'chapters' => BookChapter::with(['book.group.category'])->find($id),
            'books' => Book::with(['group.category'])->find($id),
            'book_groups' => BookGroup::with(['category'])->find($id),
            default => null,
        };
    }

    /**
     * Prepare prompt by replacing placeholders with content data
     */
    private function preparePrompt($promptTemplate, $content, $contentType)
    {
        $replacements = [];

        switch ($contentType) {
            case 'posts':
                $replacements = [
                    '{{title}}' => $content->title ?? '',
                    '{{chapter_name}}' => $content->chapter->name ?? '',
                    '{{book_name}}' => $content->chapter->book->name ?? '',
                    '{{group_name}}' => $content->chapter->book->group->name ?? '',
                    '{{category_name}}' => $content->chapter->book->group->category->name ?? '',
                ];
                break;

            case 'chapters':
                $replacements = [
                    '{{name}}' => $content->name ?? '',
                    '{{book_name}}' => $content->book->name ?? '',
                    '{{group_name}}' => $content->book->group->name ?? '',
                    '{{category_name}}' => $content->book->group->category->name ?? '',
                ];
                break;

            case 'books':
                $replacements = [
                    '{{name}}' => $content->name ?? '',
                    '{{group_name}}' => $content->group->name ?? '',
                    '{{category_name}}' => $content->group->category->name ?? '',
                ];
                break;

            case 'book_groups':
                $replacements = [
                    '{{name}}' => $content->name ?? '',
                    '{{category_name}}' => $content->category->name ?? '',
                ];
                break;
        }

        // Replace all tokens
        return str_replace(array_keys($replacements), array_values($replacements), $promptTemplate);
    }

    /**
     * Get content items based on filter
     */
    private function getContentByFilter($contentType, $filterType, $filterId)
    {
        switch ($contentType) {
            case 'posts':
                $query = Post::query();

                if ($filterType == 'chapter') {
                    $query->where('book_chapter_id', $filterId);
                } elseif ($filterType == 'book') {
                    $query->whereHas('chapter', function($q) use ($filterId) {
                        $q->where('book_id', $filterId);
                    });
                } elseif ($filterType == 'source_url') {
                    $query->where('source_url', 'like', '%' . $filterId . '%');
                } elseif ($filterType == 'ids') {
                    $ids = explode(',', $filterId);
                    $query->whereIn('id', $ids);
                } elseif ($filterType == 'missing_meta') {
                    $query->where(function($q) {
                        $q->whereNull('meta_title')
                            ->orWhereNull('meta_description')
                            ->orWhere('meta_title', '')
                            ->orWhere('meta_description', '');
                    });
                }

                return $query->take(100)->get();

            case 'chapters':
                $query = BookChapter::query();

                if ($filterType == 'book') {
                    $query->where('book_id', $filterId);
                } elseif ($filterType == 'ids') {
                    $ids = explode(',', $filterId);
                    $query->whereIn('id', $ids);
                } elseif ($filterType == 'missing_description') {
                    $query->where(function($q) {
                        $q->whereNull('description')
                            ->orWhere('description', '');
                    });
                }

                return $query->take(100)->get();

            case 'books':
                $query = Book::query();

                if ($filterType == 'group') {
                    $query->where('book_group_id', $filterId);
                } elseif ($filterType == 'ids') {
                    $ids = explode(',', $filterId);
                    $query->whereIn('id', $ids);
                } elseif ($filterType == 'missing_description') {
                    $query->where(function($q) {
                        $q->whereNull('description')
                            ->orWhere('description', '');
                    });
                }

                return $query->take(100)->get();

            case 'book_groups':
                $query = BookGroup::query();

                if ($filterType == 'category') {
                    $query->where('category_id', $filterId);
                } elseif ($filterType == 'ids') {
                    $ids = explode(',', $filterId);
                    $query->whereIn('id', $ids);
                } elseif ($filterType == 'missing_description') {
                    $query->where(function($q) {
                        $q->whereNull('description')
                            ->orWhere('description', '');
                    });
                }

                return $query->take(100)->get();

            default:
                return collect();
        }
    }

    /**
     * Update content SEO data
     */
    private function updateContentSEO($content, $contentType, $result)
    {
        try {
            switch ($contentType) {
                case 'posts':
//                    $content->meta_title = $result['meta_title'] ?? null;
//                    $content->meta_description = $result['meta_description'] ?? null;

                    DB::table('posts')->where('id', $content->id)->update([
                        'meta_title' => $result['meta_title'] ?? null,
                        'meta_description' => $result['meta_description'] ?? null
                    ]);
                    break;

                case 'chapters':
                case 'books':
                case 'book_groups':
                    $content->description = $result;
                    break;
            }

            $content->save();
            return true;
        } catch (\Exception $e) {
            Log::error('Error updating content SEO', [
                'content_type' => $contentType,
                'content_id' => $content->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get query for specific content type
     */
    private function getQueryForType($type)
    {
        switch ($type) {
            case 'posts':
                return Post::whereNull('meta_title')
                    ->orWhereNull('meta_description')
                    ->orWhere('meta_title', '')
                    ->orWhere('meta_description', '')
                    ->with(['chapter.book.group.category']);

            case 'chapters':
                return BookChapter::whereNull('description')
                    ->orWhere('description', '')
                    ->with(['book.group.category']);

            case 'books':
                return Book::whereNull('description')
                    ->orWhere('description', '')
                    ->with(['group.category']);

            case 'book_groups':
                return BookGroup::whereNull('description')
                    ->orWhere('description', '')
                    ->with(['category']);

            default:
                return Post::query(); // Fallback
        }
    }

    /**
     * Apply search to query
     */
    private function applySearchToQuery($query, $type, $search)
    {
        switch ($type) {
            case 'posts':
                $query->where('title', 'like', "%{$search}%");
                break;

            case 'chapters':
            case 'books':
            case 'book_groups':
                $query->where('name', 'like', "%{$search}%");
                break;
        }

        return $query;
    }

    /**
     * Get missing SEO data counts
     */
    private function getMissingSEOData()
    {
        return [
            'posts_no_meta' => Post::where(function($q) {
                $q->whereNull('meta_title')
                    ->orWhereNull('meta_description')
                    ->orWhere('meta_title', '')
                    ->orWhere('meta_description', '');
            })->count(),

            'chapters_no_desc' => BookChapter::where(function($q) {
                $q->whereNull('description')
                    ->orWhere('description', '');
            })->count(),

            'books_no_desc' => Book::where(function($q) {
                $q->whereNull('description')
                    ->orWhere('description', '');
            })->count(),

            'book_groups_no_desc' => BookGroup::where(function($q) {
                $q->whereNull('description')
                    ->orWhere('description', '');
            })->count(),
        ];
    }

    /**
     * Get SEO progress stats
     */
    private function getSEOProgress()
    {
        $postsMissing = Post::where(function($q) {
            $q->whereNull('meta_title')
                ->orWhereNull('meta_description')
                ->orWhere('meta_title', '')
                ->orWhere('meta_description', '');
        })->count();

        $postsTotal = Post::count();

        $chaptersMissing = BookChapter::where(function($q) {
            $q->whereNull('description')
                ->orWhere('description', '');
        })->count();

        $chaptersTotal = BookChapter::count();

        $booksMissing = Book::where(function($q) {
            $q->whereNull('description')
                ->orWhere('description', '');
        })->count();

        $booksTotal = Book::count();

        $groupsMissing = BookGroup::where(function($q) {
            $q->whereNull('description')
                ->orWhere('description', '');
        })->count();

        $groupsTotal = BookGroup::count();

        return [
            'posts' => [
                'completed' => $postsTotal - $postsMissing,
                'total' => $postsTotal,
                'percentage' => $postsTotal > 0 ? round(($postsTotal - $postsMissing) / $postsTotal * 100) : 0
            ],
            'chapters' => [
                'completed' => $chaptersTotal - $chaptersMissing,
                'total' => $chaptersTotal,
                'percentage' => $chaptersTotal > 0 ? round(($chaptersTotal - $chaptersMissing) / $chaptersTotal * 100) : 0
            ],
            'books' => [
                'completed' => $booksTotal - $booksMissing,
                'total' => $booksTotal,
                'percentage' => $booksTotal > 0 ? round(($booksTotal - $booksMissing) / $booksTotal * 100) : 0
            ],
            'book_groups' => [
                'completed' => $groupsTotal - $groupsMissing,
                'total' => $groupsTotal,
                'percentage' => $groupsTotal > 0 ? round(($groupsTotal - $groupsMissing) / $groupsTotal * 100) : 0
            ],
        ];
    }
}
