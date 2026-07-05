<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Usage in routes: ->middleware('role:super_admin,salary_admin')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Map role aliases to Spatie role names
        $roleMap = [
            'super_admin'         => 'System & Administration',
            'inventory_admin'     => 'Personnel Records',
            'salary_admin'        => 'Welfare & Benefits',
            'appointment_admin'   => 'Appointment',
            'appointment_encoder' => 'Appointment Encoder',
            'performance_admin'   => 'Performance Management',
            'viewer'              => 'Viewer',
        ];

        $hasAccess = false;
        foreach ($roles as $r) {
            $mappedRole = $roleMap[$r] ?? $r;
            if ($user->hasRole($mappedRole)) {
                $hasAccess = true;
                break;
            }
        }

        if (!$hasAccess) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}
