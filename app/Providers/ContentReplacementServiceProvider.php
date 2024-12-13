<?php

namespace App\Providers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;

class ContentReplacementServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        //Artisan::call('content:replace-urls');
    }
}
