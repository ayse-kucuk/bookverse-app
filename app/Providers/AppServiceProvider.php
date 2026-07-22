<?php

namespace App\Providers;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
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
        Vite::prefetch(concurrency: 3);

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        View::composer('partials.site-nav', function ($view) {
            if (! auth()->check()) {
                return;
            }

            $user = auth()->user();

            $view->with('navNotifications', $user->notifications()
                ->unread()
                ->with(['actor', 'post'])
                ->latest()
                ->limit(8)
                ->get());

            $view->with('navUnreadCount', $user->unreadNotificationsCount());
        });
    }
}
