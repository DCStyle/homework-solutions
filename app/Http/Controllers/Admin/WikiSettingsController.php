<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\BookGroup;
use App\Models\WikiSetting;
use App\Services\AI\AIServiceFactory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class WikiSettingsController extends Controller
{
    /**
     * Display settings page for the Wiki Q&A system.
     */
    public function index(): View
    {
        $settings = WikiSetting::pluck('value', 'key')->toArray();
        $aiProviders = AIServiceFactory::getActiveProviders();

        $categories = Category::all();
        $bookGroups = BookGroup::all();

        return view('admin.wiki.settings', compact('settings', 'aiProviders', 'categories', 'bookGroups'));
    }

    /**
     * Update the wiki settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'default_ai_provider' => 'required|string',
            'vector_search_enabled' => 'boolean',
            'vector_search_threshold' => 'numeric|min:0|max:1',
            'moderation_enabled' => 'boolean',
            'auto_approve_questions' => 'boolean',
            'max_questions_per_day' => 'integer|min:0',
            'max_comments_per_day' => 'integer|min:0',
        ]);

        foreach ($validated as $key => $value) {
            WikiSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return redirect()->route('admin.wiki.settings')->with('success', 'Wiki settings updated successfully');
    }

    /**
     * Display the question moderation page.
     */
    public function moderation(): View
    {
        $pendingQuestions = \App\Models\WikiQuestion::where('status', 'pending')
            ->with(['user', 'category'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.wiki.moderation', compact('pendingQuestions'));
    }

    /**
     * Approve a question.
     */
    public function approveQuestion(int $questionId): RedirectResponse
    {
        $question = \App\Models\WikiQuestion::findOrFail($questionId);
        $question->status = 'published';
        $question->save();

        // Generate embeddings for the approved question
        app(\App\Services\WikiAIService::class)->generateEmbeddingForQuestion($question);

        return redirect()->route('admin.wiki.moderation')->with('success', 'Question approved successfully');
    }

    /**
     * Reject a question.
     */
    public function rejectQuestion(int $questionId): RedirectResponse
    {
        $question = \App\Models\WikiQuestion::findOrFail($questionId);
        $question->status = 'hidden';
        $question->save();

        return redirect()->route('admin.wiki.moderation')->with('success', 'Question rejected successfully');
    }

    /**
     * Display the question management page.
     */
    public function questions(): View
    {
        $questions = \App\Models\WikiQuestion::with(['user', 'category'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.wiki.questions', compact('questions'));
    }
}
