<?php

namespace App\Providers;

use App\Events\Wiki\QuestionCreated;
use App\Events\Wiki\QuestionStatusChanged;
use App\Events\Wiki\AnswerCreated;
use App\Events\Wiki\CommentCreated;
use App\Listeners\Wiki\ProcessNewQuestion;
use App\Listeners\Wiki\ProcessQuestionStatusChange;
use App\Listeners\Wiki\ProcessNewAnswer;
use App\Listeners\Wiki\ProcessNewComment;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        // Wiki Events
        QuestionCreated::class => [
            ProcessNewQuestion::class,
        ],
        QuestionStatusChanged::class => [
            ProcessQuestionStatusChange::class,
        ],
        AnswerCreated::class => [
            ProcessNewAnswer::class,
        ],
        CommentCreated::class => [
            ProcessNewComment::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        // Model event listeners for Wiki system

        // When a question is created
        \App\Models\WikiQuestion::created(function ($question) {
            event(new QuestionCreated($question));
        });

        // When a question is updated
        \App\Models\WikiQuestion::updated(function ($question) {
            if ($question->isDirty('status')) {
                event(new QuestionStatusChanged(
                    $question,
                    $question->getOriginal('status'),
                    $question->status
                ));
            }
        });

        // When an answer is created
        \App\Models\WikiAnswer::created(function ($answer) {
            event(new AnswerCreated($answer));
        });

        // When a comment is created
        \App\Models\WikiComment::created(function ($comment) {
            event(new CommentCreated($comment));
        });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
