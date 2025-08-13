<?php

namespace App\Providers;

use App\Support\CustomQueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Define task permissions
        Gate::define('tasks.read', function ($user) {
            return true; // Allow all authenticated users to read tasks
        });

        Gate::define('tasks.create', function ($user) {
            return true; // Allow all authenticated users to create tasks
        });

        Gate::define('tasks.update', function ($user) {
            return true; // Allow all authenticated users to update tasks
        });

        Gate::define('tasks.delete', function ($user) {
            return true; // Allow all authenticated users to delete tasks
        });

        Gate::define('tasks.restore', function ($user) {
            return true; // Allow all authenticated users to restore tasks
        });

        Gate::define('tasks.forceDelete', function ($user) {
            return true; // Allow all authenticated users to force delete tasks
        });

        Gate::define('tasks.archive', function ($user) {
            return true; // Allow all authenticated users to archive tasks
        });

        Gate::define('tasks.export', function ($user) {
            return true; // Allow all authenticated users to export tasks
        });


        Builder::mixin(new CustomQueryBuilder);

    }
}
