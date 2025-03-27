<?php

namespace App\Observers;

use App\Models\WikiQuestion;
use App\Events\Wiki\QuestionCreated;
use App\Events\Wiki\QuestionStatusChanged;
use App\Services\WikiAIService;
use App\Services\WikiEventService;
use Illuminate\Support\Facades\Log;

class WikiQuestionObserver
{
    protected $aiService;
    protected $eventService;

    /**
     * Create a new observer instance.
     *
     * @param WikiAIService $aiService
     * @param WikiEventService $eventService
     * @return void
     */
    public function __construct(WikiAIService $aiService, WikiEventService $eventService)
    {
        $this->aiService = $aiService;
        $this->eventService = $eventService;
    }

    /**
     * Handle the WikiQuestion "created" event.
     *
     * @param  \App\Models\WikiQuestion  $question
     * @return void
     */
    public function created(WikiQuestion $question)
    {
        try {
            Log::info('Question created', [
                'question_id' => $question->id,
                'user_id' => $question->user_id,
                'title' => $question->title
            ]);

            // Dispatch the QuestionCreated event
            event(new QuestionCreated($question));

            // Notify through the event service
            $this->eventService->onQuestionCreated($question);

            // Generate embedding for the question if enabled
            if ((bool) \App\Models\WikiSetting::get('embedding_enabled', '1')) {
                try {
                    $this->aiService->generateEmbeddingForQuestion($question);
                } catch (\Exception $e) {
                    Log::error('Error generating embedding in observer: ' . $e->getMessage(), [
                        'question_id' => $question->id,
                        'trace' => $e->getTraceAsString()
                    ]);
                    // Don't throw so we don't disrupt the main flow
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in question created observer: ' . $e->getMessage(), [
                'question_id' => $question->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Handle the WikiQuestion "updated" event.
     *
     * @param  \App\Models\WikiQuestion  $question
     * @return void
     */
    public function updated(WikiQuestion $question)
    {
        try {
            // Handle status changes
            if ($question->isDirty('status')) {
                $oldStatus = $question->getOriginal('status');
                $newStatus = $question->status;

                Log::info('Question status changed', [
                    'question_id' => $question->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus
                ]);

                // Dispatch the QuestionStatusChanged event
                event(new QuestionStatusChanged($question, $oldStatus, $newStatus));

                // Notify through the event service
                $this->eventService->onQuestionStatusChanged($question, $oldStatus, $newStatus);
            }

            // If content was updated, update the embedding
            if ($question->isDirty('content') || $question->isDirty('title')) {
                if ((bool) \App\Models\WikiSetting::get('embedding_enabled', '1')) {
                    try {
                        $this->aiService->generateEmbeddingForQuestion($question);

                        Log::info('Updated embedding for question after content change', [
                            'question_id' => $question->id
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Error updating embedding: ' . $e->getMessage(), [
                            'question_id' => $question->id,
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in question updated observer: ' . $e->getMessage(), [
                'question_id' => $question->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Handle the WikiQuestion "deleted" event.
     *
     * @param  \App\Models\WikiQuestion  $question
     * @return void
     */
    public function deleted(WikiQuestion $question)
    {
        try {
            Log::info('Question deleted', [
                'question_id' => $question->id,
                'title' => $question->title
            ]);

            // Delete related content
            if ($question->isForceDeleting()) {
                // Hard delete - remove all related content
                $question->answers()->delete();
                $question->comments()->delete();
                $question->embedding()->delete();
            }
        } catch (\Exception $e) {
            Log::error('Error in question deleted observer: ' . $e->getMessage(), [
                'question_id' => $question->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Handle the WikiQuestion "restored" event.
     *
     * @param  \App\Models\WikiQuestion  $question
     * @return void
     */
    public function restored(WikiQuestion $question)
    {
        try {
            Log::info('Question restored', [
                'question_id' => $question->id,
                'title' => $question->title
            ]);

            // Re-generate embedding if needed
            if ((bool) \App\Models\WikiSetting::get('embedding_enabled', '1') && !$question->embedding) {
                try {
                    $this->aiService->generateEmbeddingForQuestion($question);
                } catch (\Exception $e) {
                    Log::error('Error generating embedding for restored question: ' . $e->getMessage(), [
                        'question_id' => $question->id,
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in question restored observer: ' . $e->getMessage(), [
                'question_id' => $question->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Handle the WikiQuestion "force deleted" event.
     *
     * @param  \App\Models\WikiQuestion  $question
     * @return void
     */
    public function forceDeleted(WikiQuestion $question)
    {
        try {
            Log::info('Question force deleted', [
                'question_id' => $question->id,
                'title' => $question->title
            ]);

            // Ensure related content is removed
            $question->answers()->forceDelete();
            $question->comments()->forceDelete();
            $question->embedding()->forceDelete();
        } catch (\Exception $e) {
            Log::error('Error in question force deleted observer: ' . $e->getMessage(), [
                'question_id' => $question->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
