<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\RetentionSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Module 1B.5 — the portable, searchable Records Retention & Disposal
 * Matrix. Editable only by Records-authorized sessions (isSuperAdmin or the
 * 'edit Records Retention Matrix' bit); every other authenticated user can
 * view/search/export it as a reference.
 */
class RetentionScheduleController extends Controller
{
    private const EDIT_PERMISSION = 'edit Records Retention Matrix';

    public function index(Request $request)
    {
        $query = RetentionSchedule::query()->orderBy('record_series_title');

        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(function ($qq) use ($q) {
                $qq->where('record_series_title', 'like', "%{$q}%")
                   ->orWhere('description', 'like', "%{$q}%")
                   ->orWhere('nap_grds_item_ref', 'like', "%{$q}%");
            });
        }
        if ($request->filled('category')) {
            $query->where('record_category', $request->string('category'));
        }
        if ($request->filled('disposition')) {
            $query->where('disposition_action', $request->string('disposition'));
        }

        $rows = $query->paginate(25)->withQueryString();
        $canEdit = Auth::user()->isSuperAdmin() || Auth::user()->can(self::EDIT_PERMISSION);

        return view('retention-schedule.index', compact('rows', 'canEdit'));
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->isSuperAdmin() || Auth::user()->can(self::EDIT_PERMISSION), 403);

        $data = $this->validated($request);
        $data['last_updated_by'] = Auth::id();
        $series = RetentionSchedule::create($data);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Created Retention Schedule Entry',
            'description' => "Added retention series \"{$series->record_series_title}\".",
        ]);

        return back()->with('success', 'Retention schedule entry added.');
    }

    public function update(Request $request, RetentionSchedule $retentionSchedule)
    {
        abort_unless(Auth::user()->isSuperAdmin() || Auth::user()->can(self::EDIT_PERMISSION), 403);

        $data = $this->validated($request);
        $data['last_updated_by'] = Auth::id();
        $retentionSchedule->update($data);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Updated Retention Schedule Entry',
            'description' => "Updated retention series \"{$retentionSchedule->record_series_title}\".",
        ]);

        return back()->with('success', 'Retention schedule entry updated.');
    }

    public function exportCsv()
    {
        $rows = RetentionSchedule::orderBy('record_series_title')->get();

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Series Title', 'Category', 'Time Value', 'Active Period', 'Storage Period', 'Total Retention', 'Disposition', 'Legal Basis', 'NAP GRDS Ref']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->record_series_title, $r->record_category, $r->time_value,
                    $r->active_period, $r->storage_period, $r->total_retention,
                    $r->disposition_action, $r->legal_basis, $r->nap_grds_item_ref,
                ]);
            }
            fclose($out);
        };

        return response()->streamDownload($callback, 'retention-schedule.csv', ['Content-Type' => 'text/csv']);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'record_series_title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'record_category' => 'required|string|max:30',
            'time_value' => 'required|in:PERMANENT,TEMPORARY',
            'active_period' => 'nullable|string|max:255',
            'storage_period' => 'nullable|string|max:255',
            'total_retention' => 'nullable|string|max:255',
            'disposition_action' => 'required|in:PERMANENT_PRESERVATION,DESTRUCTION,REVIEW',
            'legal_basis' => 'nullable|string|max:255',
            'nap_grds_item_ref' => 'nullable|string|max:100',
            'remarks' => 'nullable|string',
        ]);
    }
}
