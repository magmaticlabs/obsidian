<?php

namespace MagmaticLabs\Obsidian\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        Schema::defaultStringLength(191);

        Passport::ignoreMigrations();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        Passport::$unserializesCookies = true;
    }
}
