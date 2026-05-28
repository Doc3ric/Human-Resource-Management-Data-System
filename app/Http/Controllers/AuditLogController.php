<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;
use App\Models\ActivityLog as CustomActivityLog;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        // ── 1. SPATIE ACTIVITY LOGS ──
        $query = Activity::with(['causer', 'subject'])->latest();

        // Fetch all registered users for the dropdown
        $users = \App\Models\User::select('id', 'name')->orderBy('name')->get();

        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id)->where('causer_type', \App\Models\User::class);
        } elseif ($request->filled('user')) {
            $query->whereHas('causer', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->user . '%')
                  ->orWhere('username', 'like', '%' . $request->user . '%');
            });
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        $logs = $query->paginate(20, ['*'], 'spatie_page')->withQueryString();

        // ── 2. CUSTOM ACTIVITY LOGS ──
        $customQuery = \App\Models\ActivityLog::with('user')->latest();
        
        if ($request->filled('user_id')) {
            $customQuery->where('user_id', $request->user_id);
        } elseif ($request->filled('user')) {
            $customQuery->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->user . '%')
                  ->orWhere('username', 'like', '%' . $request->user . '%');
            });
        }
        
        $customLogs = $customQuery->paginate(20, ['*'], 'custom_page')->withQueryString();

        // ── STATS ──
        $totalActions = Activity::count() + \App\Models\ActivityLog::count();
        $createdCount = Activity::where('event', 'created')->count();
        $updatedCount = Activity::where('event', 'updated')->count();
        $deletedCount = Activity::where('event', 'deleted')->count();

        return view('audit-logs.index', compact('logs', 'customLogs', 'totalActions', 'createdCount', 'updatedCount', 'deletedCount', 'users'));
    }

    /**
     * Undo a Spatie "updated" activity log entry by restoring the previous values
     * stored in properties->old back onto the subject model.
     *
     * Only "updated" events on PlantillaRecord, CasualEmployee, and JobOrder are
     * supported. The undo is non-destructive: it only touches the fields that were
     * recorded in the old snapshot.
     */
    public function undo(Activity $activity)
    {
        // Only updated events can be undone (created/deleted don't have 'old')
        if ($activity->event !== 'updated') {
            return back()->with('error', 'Only "updated" events can be undone.');
        }

        $old = $activity->properties['old'] ?? null;
        if (empty($old)) {
            return back()->with('error', 'No previous snapshot found for this log entry.');
        }

        // Resolve the subject model
        $subject = $activity->subject;
        if (!$subject) {
            return back()->with('error', 'The original record no longer exists and cannot be restored.');
        }

        // Safety guard: only allow undo on our main employee tables
        $allowed = [
            \App\Models\PlantillaRecord::class,
            \App\Models\CasualEmployee::class,
            \App\Models\JobOrder::class,
        ];
        if (!in_array(get_class($subject), $allowed)) {
            return back()->with('error', 'Undo is not supported for this record type.');
        }

        // Remove employee_code from the undo payload — it would violate the unique
        // constraint if the code changed after this log was written.
        unset($old['employee_code']);

        // Apply the old values (only the fields that were changed at that time)
        $subject->updateQuietly($old); // updateQuietly skips creating another activity log

        // Write a record of the undo in the custom activity log so it's traceable
        CustomActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'Undid Edit',
            'description' => 'Reverted Spatie audit log #' . $activity->id
                           . ' on ' . class_basename($subject) . ' #' . $subject->id
                           . ' (' . $activity->description . ')',
        ]);

        return back()->with('success',
            'Record successfully reverted to its previous state (before audit log #' . $activity->id . ').');
    }
}
