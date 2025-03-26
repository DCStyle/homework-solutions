<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\BookGroup;
use App\Models\WikiQuestion;
use App\Models\WikiAnswer;
use App\Models\WikiComment;
use App\Services\WikiAIService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

ini_set('output_buffering', 'off');
ini_set('zlib.output_compression', false);
ini_set('implicit_flush', true);

class WikiQuestionController extends Controller
{
    protected WikiAIService $aiService;

    public function __construct(WikiAIService $aiService)
    {
        $this->aiService = $aiService;
        $this->middleware('auth')->except(['stream']);
    }

    /**
     * Show the form for creating a new question.
     */
    public function create(): View
    {
        $categories = Category::all();
        $bookGroups = BookGroup::all();

        return view('wiki.questions.create', [
            'categories' => $categories,
            'bookGroups' => $bookGroups,
        ]);
    }

    /**
     * Store a newly created question in storage.
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'book_group_id' => 'nullable|exists:book_groups,id',
        ]);

        // Get category and book group names for context
        $categoryName = null;
        $bookGroupName = null;
        $category = null;

        if (isset($validated['category_id'])) {
            $category = Category::find($validated['category_id']);
            $categoryName = $category ? $category->name : null;
        }

        if (isset($validated['book_group_id']) && $validated['book_group_id']) {
            $bookGroup = BookGroup::find($validated['book_group_id']);
            $bookGroupName = $bookGroup ? $bookGroup->name : null;
        }

        // Generate title using AI
        $title = $this->aiService->generateQuestionTitle(
            $validated['content'],
            $categoryName,
            $bookGroupName
        );

        $question = new WikiQuestion();
        $question->fill($validated);
        $question->title = $title;
        $question->user_id = Auth::id();
        $question->status = 'pending';
        $question->save();

        // If this is an AJAX request, return JSON response
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Câu hỏi của bạn đã được gửi và đang được xử lý.',
                'question_id' => $question->id,
                'question_slug' => $question->slug,
                'category_slug' => $category ? $category->slug : '',
                'question_title' => $question->title,
                'question_url' => route('wiki.show', [$category->slug, $question->slug])
            ]);
        }

        // For non-AJAX requests, redirect as usual
        return redirect()->route('wiki.questions.success', $question)
            ->with('message', 'Câu hỏi của bạn đã được gửi và đang được xử lý.');
    }

    /**
     * Show the success page after submitting a question.
     */
    public function success(WikiQuestion $question): View
    {
        return view('wiki.questions.success', [
            'question' => $question,
        ]);
    }

    /**
     * Store a new comment for a question.
     */
    public function storeComment(Request $request, WikiQuestion $question): RedirectResponse
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:wiki_comments,id',
        ]);

        $comment = new WikiComment();
        $comment->fill($validated);
        $comment->question_id = $question->id;
        $comment->user_id = Auth::id();
        $comment->save();

        return back()->with('message', 'Comment added successfully.');
    }

    public function stream(WikiQuestion $question)
    {
        // Set proper headers for SSE with UTF-8 encoding
        header('Content-Type: text/event-stream; charset=UTF-8');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no'); // Disable Nginx buffering if you use Nginx

        // Generate the answer
        $content = app(WikiAIService::class)->generateAnswer($question);

        // Save the answer to the database
        $answer = new WikiAnswer();
        $answer->question_id = $question->id;
        $answer->content = $content;
        $answer->is_ai = true;
        $answer->save();

        // Update question status to published
        $question->status = 'published';
        $question->save();

        // Process the content with Vietnamese character handling
        $content = $this->processVietnameseContent($content);

        // Start the stream
        echo "data: <START_CONTENT>\n\n";
        flush();

        // Stream in safe-sized chunks that don't break UTF-8 sequences
        $chunks = $this->createSafeUtf8Chunks($content, 200); // Safe chunk size

        foreach ($chunks as $chunk) {
            echo "data: $chunk\n\n";
            flush();
            usleep(50000); // Small delay to simulate typing
        }

        echo "data: <END_CONTENT>\n\n";
        echo "event: DONE\ndata: {\"status\":\"complete\"}\n\n";
        flush();
    }

    /**
     * Create chunks that don't break UTF-8 character sequences
     */
    private function createSafeUtf8Chunks($text, $size)
    {
        $chunks = [];
        $len = mb_strlen($text, 'UTF-8');

        for ($i = 0; $i < $len; $i += $size) {
            $chunks[] = mb_substr($text, $i, $size, 'UTF-8');
        }

        return $chunks;
    }

    /**
     * Process Vietnamese content for proper encoding
     */
    private function processVietnameseContent($content)
    {
        // Use the OpenRouterResponseFormatter if available
        if (class_exists('App\\Helpers\\OpenRouterResponseFormatter')) {
            return \App\Helpers\OpenRouterResponseFormatter::formatResponse($content);
        }

        // Basic fallback if formatter isn't available
        return $content;
    }

    /**
     * Get fallback answer when AI generation fails.
     */
    private function getFallbackAnswer(WikiQuestion $question): string
    {
        $categoryName = $question->category ? $question->category->name : 'Uncategorized';

        return <<<EOT
<h2>Câu trả lời cho: {$question->title}</h2>

<p>Chúng tôi đang xử lý câu hỏi của bạn. Đây là một câu trả lời tạm thời.</p>

<p>Câu hỏi của bạn thuộc danh mục <strong>{$categoryName}</strong>. Một câu trả lời đầy đủ đang được xây dựng và sẽ sớm được cập nhật.</p>

<h3>Các bước tiếp theo</h3>
<ul>
    <li>Kiểm tra lại sau vài phút để xem câu trả lời đầy đủ</li>
    <li>Bạn có thể bổ sung thêm thông tin vào câu hỏi nếu cần</li>
    <li>Xem các câu hỏi liên quan trong cùng danh mục</li>
</ul>

<p>Cảm ơn bạn đã sử dụng hệ thống hỏi đáp của chúng tôi!</p>
EOT;
    }

    /**
     * Check the saved answer for a question.
     */
    public function checkAnswer(WikiQuestion $question)
    {
        $answer = $question->answers()->where('is_ai', true)->first();

        if (!$answer) {
            return response()->json([
                'status' => 'error',
                'message' => 'No answer found',
                'question_id' => $question->id,
                'question_status' => $question->status
            ]);
        }

        return response()->json([
            'status' => 'success',
            'answer_id' => $answer->id,
            'content_length' => strlen($answer->content),
            'content_preview' => substr($answer->content, 0, 200),
            'question_status' => $question->status,
            'created_at' => $answer->created_at->toDateTimeString(),
            'updated_at' => $answer->updated_at->toDateTimeString()
        ]);
    }
}
