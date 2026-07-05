<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\PlantillaRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest();

        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function($q) use ($term) {
                $q->where('action', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%")
                  ->orWhereHas('user', function($uq) use ($term) {
                      $uq->where('name', 'like', "%{$term}%");
                  });
            });
        }

        if ($request->filled('action_filter')) {
            $query->where('action', $request->input('action_filter'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $logs = $query->paginate(20)->withQueryString();

        $totalActions  = ActivityLog::count();
        $restoredCount = ActivityLog::where('action', 'Restored Employee')->count();
        $vacatedCount  = ActivityLog::where('action', 'Vacated Position')->count();
        
        $actions = ActivityLog::select('action')->distinct()->pluck('action');

        return view('activity-logs.index', compact('logs', 'totalActions', 'restoredCount', 'vacatedCount', 'actions'));
    }

    /**
     * Undo a "Vacated Position" action by restoring the employee snapshot
     * back to the plantilla record and marking it as filled again.
     */
    public function undo(ActivityLog $log)
    {
        // Only "Vacated Position" logs with a snapshot can be undone
        if ($log->action !== 'Vacated Position' || empty($log->snapshot)) {
            return back()->with('error', 'This action cannot be undone.');
        }

        $snap = $log->snapshot;
        $record = PlantillaRecord::find($snap['plantilla_record_id']);

        if (!$record) {
            return back()->with('error', 'The plantilla record no longer exists and cannot be restored.');
        }

        if (!$record->is_vacant) {
            return back()->with('error', 'This position is already filled. Undo is not applicable.');
        }

        // Restore all personal fields from the snapshot
        $record->update([
            'is_vacant'                 => false,
            'last_name'                 => $snap['last_name'],
            'first_name'                => $snap['first_name'],
            'middle_name'               => $snap['middle_name'],
            'sex'                       => $snap['sex'],
            'date_of_birth'             => $snap['date_of_birth'],
            'tin'                       => $snap['tin'],
            'date_original_appointment' => $snap['date_original_appointment'],
            'date_last_promotion'       => $snap['date_last_promotion'],
            'date_last_nolp'            => $snap['date_last_nolp'],
            'employment_status'         => $snap['employment_status'],
            'civil_service_eligibility' => $snap['civil_service_eligibility'],
            'remarks_annotation'        => $snap['remarks_annotation'],
            'gsis_bp_number'            => $snap['gsis_bp_number'],
            'umid'                      => $snap['umid'],
            'is_pwd'                    => $snap['is_pwd'] ?? false,
            'indigenous_people'         => $snap['indigenous_people'],
            'solo_parent'               => $snap['solo_parent'],
            'is_apprehended'            => $snap['is_apprehended'] ?? false,
            'retired_at'                => $snap['retired_at'],
        ]);

        $restoredName = trim($snap['first_name'] . ' ' . $snap['last_name']);

        // Log the undo action and nullify the snapshot so it cannot be undone twice
        if (Auth::check()) {
            ActivityLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'Restored Employee',
                'description' => 'Restored "' . $restoredName . '" to Item ' . $record->item_no_new . ' (Undo of Vacated Position)',
            ]);
        }

        // Clear the snapshot from the original log to prevent double-undo
        $log->update(['snapshot' => null]);

        return redirect()->route('activity-logs.index')
            ->with('success', 'Employee "' . $restoredName . '" has been restored to Item ' . $record->item_no_new . '.');
    }

    /**
     * Mark all recent activities as read by updating the user's last_notif_read_at timestamp.
     */
    public function markAsRead()
    {
        $user = Auth::user();
        if ($user) {
            $user->last_notif_read_at = now();
            // Since we added it to users table but didn't put it in $fillable, we can use simple save
            $user->save();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 401);
    }
}
