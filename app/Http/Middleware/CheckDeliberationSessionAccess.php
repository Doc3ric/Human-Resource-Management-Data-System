<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\HrmpsbPanelMember;
use App\Models\Applicant;

class CheckDeliberationSessionAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $applicant = $request->route('applicant');
        if (!$applicant) {
            return $next($request);
        }

        if (is_numeric($applicant)) {
            $applicant = Applicant::find($applicant);
        }

        if (!$applicant) {
            return $next($request);
        }

        $user = $request->user();
        $position = $applicant->position_applied;

        if ($user->isSuperAdmin() || $user->isAppointmentAdmin() || $user->isInventoryAdmin()) {
            return $next($request);
        }

        $hasAccess = HrmpsbPanelMember::where('user_id', $user->id)
            ->where('position_applied', $position)
            ->where('is_active', true)
            ->exists();
        
        if (!$hasAccess) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'You do not have access to this deliberation session. Only designated HRMPSB/TWG members for this position may enter.'], 403);
            }
            return redirect()->route('recruitment.deliberation.list')
                ->withErrors(['access' => 'You do not have access to the deliberation session for this position.']);
        }

        return $next($request);
    }
}
