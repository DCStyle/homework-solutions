<?php

namespace App\Providers;

use App\Repositories\AnswerRepository;
use App\Repositories\CommentRepository;
use App\Repositories\QuestionRepository;
use App\Services\WikiAIService;
use App\Services\WikiSearchService;
use Illuminate\Support\ServiceProvider;

class WikiServiceProvider extends ServiceProvider
{
    /**
     * Register any application services related to the Wiki system.
     *
     * @return void
     */
    public function register()
    {
        // Register repositories
        $this->app->singleton(QuestionRepository::class, function ($app) {
            return new QuestionRepository($app->make(WikiAIService::class));
        });

        $this->app->singleton(AnswerRepository::class, function ($app) {
            return new AnswerRepository();
        });

        $this->app->singleton(CommentRepository::class, function ($app) {
            return new CommentRepository();
        });

        // Register services
        $this->app->singleton(WikiSearchService::class, function ($app) {
            return new WikiSearchService();
        });
    }

    /**
     * Bootstrap any application services related to the Wiki system.
     *
     * @return void
     */
    public function boot()
    {
        // Register policies
        $this->registerPolicies();

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations/wiki');

        // Register asset publishing
        $this->publishes([
            __DIR__.'/../../resources/js/public/wiki' => public_path('js/wiki'),
            __DIR__.'/../../resources/css/public/wiki' => public_path('css/wiki'),
        ], 'wiki-assets');
    }

    /**
     * Register the Wiki system's policies.
     *
     * @return void
     */
    protected function registerPolicies()
    {
        // Register model policies for the Wiki system
        \Gate::policy(\App\Models\WikiQuestion::class, \App\Policies\WikiQuestionPolicy::class);
        \Gate::policy(\App\Models\WikiAnswer::class, \App\Policies\WikiAnswerPolicy::class);
        \Gate::policy(\App\Models\WikiComment::class, \App\Policies\WikiCommentPolicy::class);
    }
}
