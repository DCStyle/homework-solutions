<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WikiQuestion;
use App\Models\WikiSetting;
use App\Repositories\QuestionRepository;
use App\Services\WikiAIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WikiSettingsController extends Controller
{
    protected $questionRepository;
    protected $aiService;

    public function __construct(
        QuestionRepository $questionRepository,
        WikiAIService $aiService
    ) {
        $this->questionRepository = $questionRepository;
        $this->aiService = $aiService;
        $this->middleware('admin');
    }

    /**
     * Display the Wiki settings page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        try {
            // Get all wiki settings
            $settings = $this->getSettings();

            // Get active AI providers
            $aiProviders = app(\App\Services\AI\AIServiceFactory::class)->getActiveProviders();

            // Get question stats
            $stats = $this->getQuestionStats();

            return view('admin.wiki.settings', compact('settings', 'aiProviders', 'stats'));
        } catch (\Exception $e) {
            Log::error('Error loading wiki settings: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return view('admin.wiki.settings', [
                'settings' => [],
                'aiProviders' => [],
                'stats' => [],
                'error' => 'Error loading settings: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update the Wiki settings.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        try {
            // Validate settings
            $validated = $request->validate([
                'auto_generate_answers' => 'required|boolean',
                'default_ai_provider' => 'required|string',
                'default_api_key' => 'nullable|string',
                'moderation_enabled' => 'required|boolean',
                'max_comments_per_day' => 'required|integer|min:0',
                'approve_questions_automatic' => 'required|boolean',
                'embedding_enabled' => 'required|boolean'
            ]);

            // Save each setting
            foreach ($validated as $key => $value) {
                WikiSetting::set($key, $value);
            }

            return redirect()->route('admin.wiki.settings')
                ->with('success', 'Wiki settings updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating wiki settings: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'input' => $request->all()
            ]);

            return redirect()->route('admin.wiki.settings')
                ->with('error', 'Error updating settings: ' . $e->getMessage());
        }
    }

    /**
     * Show the moderation queue page.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function moderation(Request $request)
    {
        try {
            $status = $request->input('status', 'pending');
            $perPage = $request->input('per_page', 10);

            // Get questions by status
            $pendingQuestions = WikiQuestion::where('status', $status)
                ->with(['user', 'category', 'bookGroup'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return view('admin.wiki.moderation', [
                'pendingQuestions' => $pendingQuestions,
                'status' => $status
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading moderation page: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return view('admin.wiki.moderation', [
                'pendingQuestions' => collect(),
                'status' => $request->input('status', 'pending'),
                'error' => 'Error loading questions: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show the questions management page.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function questions(Request $request)
    {
        try {
            $status = $request->input('status', 'all');
            $perPage = $request->input('per_page', 20);
            $category = $request->input('category_id');
            $bookGroup = $request->input('book_group_id');

            // Build query
            $query = WikiQuestion::with(['user', 'category', 'bookGroup']);

            // Apply status filter
            if ($status !== 'all') {
                $query->where('status', $status);
            }

            // Apply category filter
            if ($category) {
                $query->where('category_id', $category);
            }

            // Apply book group filter
            if ($bookGroup) {
                $query->where('book_group_id', $bookGroup);
            }

            // Get questions with pagination
            $questions = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return view('admin.wiki.questions', [
                'questions' => $questions,
                'status' => $status,
                'categories' => \App\Models\Category::all(),
                'bookGroups' => \App\Models\BookGroup::all(),
                'categoryId' => $category,
                'bookGroupId' => $bookGroup
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading questions page: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return view('admin.wiki.questions', [
                'questions' => collect(),
                'status' => $request->input('status', 'all'),
                'categories' => \App\Models\Category::all(),
                'bookGroups' => \App\Models\BookGroup::all(),
                'error' => 'Error loading questions: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update question status.
     *
     * @param Request $request
     * @param WikiQuestion $question
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, WikiQuestion $question)
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:pending,published,rejected'
            ]);

            // Get old status for event handling
            $oldStatus = $question->status;

            // Update the question status
            $question = $this->questionRepository->updateStatus($question, $validated['status']);

            // Track status change for notifications/events if needed
            if ($oldStatus !== $validated['status']) {
                app(\App\Services\WikiEventService::class)->onQuestionStatusChanged($question, $oldStatus, $validated['status']);
            }

            return response()->json([
                'success' => true,
                'message' => 'Question status updated successfully',
                'question' => [
                    'id' => $question->id,
                    'status' => $question->status
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating question status: ' . $e->getMessage(), [
                'question_id' => $question->id,
                'status' => $request->input('status'),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating question status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve a question and generate AI answer.
     *
     * @param Request $request
     * @param int $questionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function approveQuestion(Request $request, int $questionId)
    {
        try {
            // Find the question
            $question = WikiQuestion::findOrFail($questionId);

            // Get old status for event handling
            $oldStatus = $question->status;

            // Update the question status
            $question = $this->questionRepository->updateStatus($question, 'published');

            // Generate AI answer if needed
            $aiAnswer = true;
            if (!$question->answers()->where('is_ai', true)->exists()) {
                try {
                    // Generate answer synchronously for immediate feedback
                    $content = $this->aiService->generateAnswer($question);

                    // Save the answer
                    $answer = new \App\Models\WikiAnswer();
                    $answer->question_id = $question->id;
                    $answer->content = $content;
                    $answer->is_ai = true;
                    $answer->save();
                } catch (\Exception $answerError) {
                    Log::error('Error generating AI answer: ' . $answerError->getMessage(), [
                        'question_id' => $question->id,
                        'trace' => $answerError->getTraceAsString()
                    ]);

                    $aiAnswer = false;
                }
            }

            // Track status change for notifications/events
            app(\App\Services\WikiEventService::class)->onQuestionStatusChanged($question, $oldStatus, 'published');

            return response()->json([
                'success' => true,
                'message' => 'Câu hỏi đã được phê duyệt và xuất bản thành công.',
                'ai_answer_status' => $aiAnswer ? 'success' : 'failed',
                'question' => [
                    'id' => $question->id,
                    'status' => $question->status
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error approving question: ' . $e->getMessage(), [
                'question_id' => $questionId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi phê duyệt câu hỏi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a question.
     *
     * @param Request $request
     * @param int $questionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function rejectQuestion(Request $request, int $questionId)
    {
        try {
            // Find the question
            $question = WikiQuestion::findOrFail($questionId);

            // Get old status for event handling
            $oldStatus = $question->status;

            // Update the question status
            $question = $this->questionRepository->updateStatus($question, 'rejected');

            // Track status change for notifications/events
            app(\App\Services\WikiEventService::class)->onQuestionStatusChanged($question, $oldStatus, 'rejected');

            return response()->json([
                'success' => true,
                'message' => 'Câu hỏi đã bị từ chối thành công.',
                'question' => [
                    'id' => $question->id,
                    'status' => $question->status
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error rejecting question: ' . $e->getMessage(), [
                'question_id' => $questionId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi từ chối câu hỏi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all wiki settings with defaults.
     *
     * @return array
     */
    private function getSettings(): array
    {
        $defaultSettings = [
            'auto_generate_answers' => '1',
            'default_ai_provider' => 'openrouter',
            'default_api_key' => '',
            'moderation_enabled' => '1',
            'max_comments_per_day' => '30',
            'approve_questions_automatic' => '0',
            'embedding_enabled' => '1'
        ];

        $settings = [];

        foreach ($defaultSettings as $key => $defaultValue) {
            $settings[$key] = WikiSetting::get($key, $defaultValue);
        }

        return $settings;
    }

    /**
     * Get question statistics.
     *
     * @return array
     */
    private function getQuestionStats(): array
    {
        return [
            'total' => WikiQuestion::count(),
            'pending' => WikiQuestion::where('status', 'pending')->count(),
            'published' => WikiQuestion::where('status', 'published')->count(),
            'rejected' => WikiQuestion::where('status', 'rejected')->count(),
            'with_ai_answers' => \App\Models\WikiAnswer::where('is_ai', true)->count(),
            'with_user_answers' => WikiQuestion::whereHas('answers', function ($query) {
                $query->where('is_ai', false);
            })->count(),
            'total_comments' => \App\Models\WikiComment::count(),
            'total_answers' => \App\Models\WikiAnswer::count()
        ];
    }
}
