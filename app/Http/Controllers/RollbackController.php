<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\PlantillaRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class RollbackController extends Controller
{
    public function index()
    {
        return view('rollback.index');
    }

    /**
     * Preview what a rollback would affect — returns JSON for the AJAX call.
     */
    public function preview(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to'   => 'required|date|after_or_equal:date_from',
            'events'    => 'required|array|min:1',
            'events.*'  => 'in:updated,created,deleted',
        ]);

        $from   = $request->date_from . ' 00:00:00';
        $to     = $request->date_to   . ' 23:59:59';
        $events = $request->events;

        $activities = Activity::where('subject_type', PlantillaRecord::class)
            ->whereIn('event', $events)
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at')
            ->with('causer')
            ->get();

        if ($activities->isEmpty()) {
            return response()->json(['count' => 0, 'records' => [], 'summary' => []]);
        }

        // Group by subject_id; keep only the FIRST activity per record (earliest change in range)
        $grouped = [];
        foreach ($activities as $act) {
            $id = $act->subject_id;
            if (!isset($grouped[$id])) {
                $grouped[$id] = $act;
            }
        }

        $summary = ['updated' => 0, 'created' => 0, 'deleted' => 0];
        $records = [];

        foreach ($grouped as $subjectId => $act) {
            $summary[$act->event] = ($summary[$act->event] ?? 0) + 1;

            $old  = $act->properties['old'] ?? [];
            $new  = $act->properties['attributes'] ?? [];

            // Fetch current record name for display
            $rec = PlantillaRecord::withTrashed()->find($subjectId);
            $name = $rec
                ? trim(($rec->first_name ?? '') . ' ' . ($rec->last_name ?? ''))
                : "(ID {$subjectId})";
            $item = $rec->item_no_new ?? '—';

            $records[] = [
                'id'          => $subjectId,
                'event'       => $act->event,
                'name'        => $name,
                'item_no'     => $item,
                'changed_by'  => $act->causer?->name ?? 'System',
                'changed_at'  => $act->created_at->format('M d, Y g:i A'),
                'fields'      => array_keys($old ?: $new),
                'old_snippet' => $this->snippet($old),
                'new_snippet' => $this->snippet($new),
            ];
        }

        return response()->json([
            'count'   => count($records),
            'summary' => $summary,
            'records' => $records,
        ]);
    }

    /**
     * Execute the rollback inside a DB transaction.
     */
    public function execute(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to'   => 'required|date|after_or_equal:date_from',
            'events'    => 'required|array|min:1',
            'events.*'  => 'in:updated,created,deleted',
        ]);

        $from   = $request->date_from . ' 00:00:00';
        $to     = $request->date_to   . ' 23:59:59';
        $events = $request->events;

        $activities = Activity::where('subject_type', PlantillaRecord::class)
            ->whereIn('event', $events)
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at')
            ->get();

        if ($activities->isEmpty()) {
            return back()->with('error', 'No changes found in the selected date range.');
        }

        // Group by subject_id — keep first activity per record
        $grouped = [];
        foreach ($activities as $act) {
            if (!isset($grouped[$act->subject_id])) {
                $grouped[$act->subject_id] = $act;
            }
        }

        $stats = ['restored' => 0, 'soft_deleted' => 0, 'revived' => 0, 'failed' => 0, 'errors' => []];

        DB::transaction(function () use ($grouped, &$stats) {
            foreach ($grouped as $subjectId => $act) {
                try {
                    $rec = PlantillaRecord::withTrashed()->find($subjectId);

                    if ($act->event === 'updated') {
                        if (!$rec) { $stats['failed']++; continue; }
                        $old = $act->properties['old'] ?? [];
                        if (!empty($old)) {
                            $rec->fill($old);
                            $rec->saveQuietly(); // skip activity log re-trigger
                            $stats['restored']++;
                        }
                    } elseif ($act->event === 'created') {
                        // Record was created in range → rollback = soft delete
                        if ($rec && !$rec->trashed()) {
                            $rec->delete();
                            $stats['soft_deleted']++;
                        }
                    } elseif ($act->event === 'deleted') {
                        // Record was deleted in range → rollback = restore
                        if ($rec && $rec->trashed()) {
                            $rec->restore();
                            $stats['revived']++;
                        }
                    }
                } catch (\Throwable $e) {
                    $stats['failed']++;
                    $stats['errors'][] = "ID {$subjectId}: " . $e->getMessage();
                }
            }
        });

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'Database Rollback',
            'description' => "Rolled back changes from {$request->date_from} to {$request->date_to}. "
                . "Restored: {$stats['restored']}, Soft-deleted: {$stats['soft_deleted']}, Revived: {$stats['revived']}, Failed: {$stats['failed']}.",
        ]);

        $msg = "Rollback complete — {$stats['restored']} record(s) restored, "
            . "{$stats['soft_deleted']} created record(s) removed, "
            . "{$stats['revived']} deleted record(s) revived.";

        if ($stats['failed'] > 0) {
            $msg .= " {$stats['failed']} record(s) failed.";
            return back()->with('warning', $msg)->with('rollback_errors', $stats['errors']);
        }

        return back()->with('success', $msg);
    }

    private function snippet(array $data): string
    {
        if (empty($data)) return '—';
        $parts = [];
        foreach (array_slice($data, 0, 3, true) as $k => $v) {
            $parts[] = "{$k}: " . (is_null($v) ? 'null' : substr((string)$v, 0, 30));
        }
        return implode(', ', $parts) . (count($data) > 3 ? '…' : '');
    }
}
