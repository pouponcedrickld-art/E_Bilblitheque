<?php

namespace App\Providers;

use App\Models\Author;
use App\Models\Category;
use App\Models\DepositRequest;
use App\Models\DepositRequestReview;
use App\Models\Notification;
use App\Models\Publisher;
use App\Models\Reference;
use App\Models\User;
use App\Observers\ActivityLogObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Attribue automatiquement toutes les permissions au rôle admin
        // Implicitly grant "admin" role all permissions
        Gate::before(function ($user, $ability) {
            if ($user->role === 'admin') {
                return true;
            }
        });

        User::observe(ActivityLogObserver::class);
        Reference::observe(ActivityLogObserver::class);
        Category::observe(ActivityLogObserver::class);
        Author::observe(ActivityLogObserver::class);
        Publisher::observe(ActivityLogObserver::class);
        DepositRequest::observe(ActivityLogObserver::class);
        DepositRequestReview::observe(ActivityLogObserver::class);
        Notification::observe(ActivityLogObserver::class);
    }
}