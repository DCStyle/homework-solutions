<?php

namespace App\Providers;

use App\Models\MenuItem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        View::composer('*', function ($view) {
            $menuItems = Cache::remember('menu_items', 3600, function () {
                return MenuItem::with([
                    'children' => function ($query) {
                        $query->orderBy('order');
                    },
                    'children.category',
                    'category'
                ])
                    ->where('active', true)
                    ->whereNull('parent_id')
                    ->orderBy('order')
                    ->get();
            });

            $view->with('menuItems', $menuItems);
        });
    }
}
