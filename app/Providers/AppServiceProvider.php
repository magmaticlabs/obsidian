<?php

namespace MagmaticLabs\Obsidian\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use MagmaticLabs\Obsidian\Domain\ProcessExecutor\ProcessExecutor;
use MagmaticLabs\Obsidian\Domain\ProcessExecutor\SymfonyProcessExecutor;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->bind(ProcessExecutor::class, SymfonyProcessExecutor::class);

        Passport::ignoreMigrations();
        Passport::withCookieSerialization();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        /** @var \Illuminate\Container\Container $validator */
        $validator = $this->app['validator'];

        // Add a validator for ensuring an attribute is not present
        $validator->extend('not_present', function () {
            // Validators are only called when the given attribute exists
            // Therefore, we always fail here
            return false;
        });

        // Add a validator for ensuring an attribute is an exact match for a value
        $validator->extend('match', function ($attribute, $value, $parameters) {
            return $value == $parameters[0];
        });

        // Add a validator for ensuring an attribute is not an  exact match for a value
        $validator->extend('not_match', function ($attribute, $value, $parameters) {
            return $value != $parameters[0];
        });

        // Add a validator for ensuring an attribute is a numeric array
        $validator->extend('numeric_array', function ($attribute, $value, $parameters) {
            if (!is_array($value)) {
                return false;
            }

            foreach (array_keys($value) as $i) {
                if (!is_numeric($i)) {
                    return false;
                }
            }

            return true;
        });
    }
}
