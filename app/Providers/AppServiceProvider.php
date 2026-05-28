<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog;

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
        // Share recent activity logs globally for notifications
        View::composer('*', function ($view) {
            if (Auth::check()) {
                $recentActivity = ActivityLog::with('user')
                    ->latest()
                    ->take(5)
                    ->get();
                
                $user = Auth::user();
                $unreadCount = 0;
                
                if ($user->last_notif_read_at) {
                    $unreadCount = ActivityLog::where('created_at', '>', $user->last_notif_read_at)->count();
                } else {
                    $unreadCount = ActivityLog::count();
                }

                $view->with('recentActivity', $recentActivity)
                     ->with('unreadNotifCount', $unreadCount);
            }
        });
    }
}
