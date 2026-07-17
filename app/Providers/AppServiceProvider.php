<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Models\ActivityLog;
use App\Models\OrganizationalUnit;

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
        // System & Administration (super admin) bypasses every Spatie
        // permission/Gate check, consistent with the isSuperAdmin() bypass
        // already used throughout this app's custom role helpers.
        Gate::before(function ($user, $ability) {
            return $user->isSuperAdmin() ? true : null;
        });

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
            
            // Share global offices list to all views for dynamic dropdowns
            try {
                // Get active units from the new System & Administration module
                $newOffices = OrganizationalUnit::where('status', 'Active')->get();
                $globalOffices = collect();

                foreach ($newOffices as $office) {
                    $globalOffices->push((object)[
                        'name' => $office->name,
                        'sub_office' => $office->sub_office
                    ]);
                }

                // Merge legacy distinct offices from plantilla_records so history isn't lost
                if (\Illuminate\Support\Facades\Schema::hasTable('plantilla_records')) {
                    $legacyOffices = \App\Models\PlantillaRecord::distinct()->whereNotNull('office_department')->pluck('office_department');
                    $legacySubOffices = \App\Models\PlantillaRecord::distinct()->whereNotNull('detailed_unit')->pluck('detailed_unit');

                    foreach ($legacyOffices as $legacyName) {
                        if (!$globalOffices->contains('name', $legacyName)) {
                            $globalOffices->push((object)[
                                'name' => $legacyName,
                                'sub_office' => null
                            ]);
                        }
                    }

                    foreach ($legacySubOffices as $legacySub) {
                        if (!$globalOffices->contains('sub_office', $legacySub)) {
                            $globalOffices->push((object)[
                                'name' => null,
                                'sub_office' => $legacySub
                            ]);
                        }
                    }
                }

                $view->with('globalOffices', $globalOffices);
            } catch (\Exception $e) {
                // If the table doesn't exist yet (e.g. during migrations), fail gracefully
                $view->with('globalOffices', collect([]));
            }
        });
    }
}
