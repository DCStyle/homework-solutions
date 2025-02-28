<?php

namespace App\Providers;

use App\Models\Category;
use App\Services\ContentMirrorService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ContentMirrorService::class, function ($app) {
            return new ContentMirrorService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if(config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Avoid running database queries during migrations
        if (!$this->app->runningInConsole() || !in_array('migrate', $_SERVER['argv'], true)) {
            // Fetch only parent categories with their children
            $categories = Category::whereNull('parent_id')->with('children')->get();
            view()->share('categories', $categories);
        }
    }
}
