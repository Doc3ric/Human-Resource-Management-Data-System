<?php

namespace App\Http\Controllers;

use App\Models\PlantillaRecord;
use App\Exports\PermanentExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ActivityLog;

class PermanentController extends Controller
{
    /** Permanent employment statuses */
    private const STATUSES = ['P', 'Permanent', 'CT', 'Co-Terminous', 'Coterminous', 'E', 'Elected'];

    /**
     * List all Permanent employees with search/filter.
     * Reads PlantillaRecord filtered to permanent employment statuses.
     */
    public function index(Request $request)
    {
        session(['last_index_url' => request()->fullUrl()]);


        $query = PlantillaRecord::whereIn('employment_status', self::STATUSES)
            ->where('is_vacant', false)
            ->whereNull('nature_of_separation')
            ->orderBy('office_department')
            ->orderBy('last_name');

        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('last_name',        'like', "%{$term}%")
                  ->orWhere('first_name',     'like', "%{$term}%")
                  ->orWhere('position_title', 'like', "%{$term}%")
                  ->orWhere('item_no_new',           'like', "%{$term}%")
                  ->orWhere('office_department', 'like', "%{$term}%");
            });
        }

        if ($request->filled('office')) {
            $query->where('office_department', 'like', '%' . $request->input('office') . '%');
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            $query->where('employment_status', $status);
        }

        if ($request->filled('position')) {
            $query->where('position_title', $request->input('position'));
        }

        if ($request->filled('detailed_unit')) {
            // Usually office_department is the only column for office here, 
            // but we can query it or simply ignore if not applicable
        }

        if ($request->filled('sex')) {
            $query->where('sex', $request->input('sex'));
        }

        $total       = (clone $query)->count();
        $maleCount   = (clone $query)->where('sex', 'M')->count();
        $femaleCount = (clone $query)->where('sex', 'F')->count();
        $vacantCount = (clone $query)->where('is_vacant', true)->count();
        
        $perPage = $request->input('per_page', 50);
        $records = $query->paginate($perPage)->withQueryString();

        $offices = PlantillaRecord::whereIn('employment_status', self::STATUSES)
            ->distinct()->orderBy('office_department')
            ->pluck('office_department')->filter()->values();
            
        $positions = PlantillaRecord::whereIn('employment_status', self::STATUSES)
            ->distinct()->pluck('position_title')->filter()->map(fn($v) => trim($v))->unique()->sortBy(fn($v) => strtolower($v))->values();

        return view('permanent.index', compact(
            'records',
            'total',
            'maleCount',
            'femaleCount',
            'vacantCount',
            'offices',
            'positions'
        ));
    }

    // ── Excel Export ──────────────────────────────────────────────────────────

    public function exportExcel(Request $request)
    {
        // Force status filter to only permanent statuses
        $filters = $request->only(['search', 'office', 'sex', 'vacant', 'status_filter']);
        $filters['statuses'] = self::STATUSES; // passed as extra; AllDataExport uses 'status' key
        // We'll use a custom query via AllDataExport with a pre-set permanent filter
        $columns  = $request->input('columns', []);
        $filename = 'permanent-employees-' . now()->format('Ymd-His') . '.xlsx';

        if (Auth::check()) {
            ActivityLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'Exported Data',
                'description' => 'Exported Permanent Employees data to Excel',
            ]);
        }

        // Use AllDataExport — it already supports the status filter properly
        // We inject a special 'permanent_only' flag via a custom wrapper
        return Excel::download(new PermanentExport($filters, $columns), $filename);
    }

    // ── PDF Export ────────────────────────────────────────────────────────────

    public function exportPdf(Request $request)
    {
        // Large dataset — DomPDF needs substantial resources for 2000+ rows
        ini_set('memory_limit', '2048M');
        set_time_limit(600);

        $columns = $request->input('columns', []);

        // Only select the columns actually needed for the PDF — avoids loading
        // all 40+ model attributes into memory for each of 2,400+ records
        $dbColumns = [
            'id', 'office_department', 'item_no_new', 'position_title',
            'salary_grade', 'step', 'base_salary_amount',
            'last_name', 'first_name', 'middle_name', 'sex',
            'date_of_birth', 'tin', 'date_original_appointment',
            'date_last_promotion', 'civil_service_eligibility',
            'employment_status', 'is_vacant',
        ];

        $statusFilter = $request->input('status_filter', 'active');

        $query = PlantillaRecord::select($dbColumns)
            ->whereIn('employment_status', self::STATUSES)
            ->where('is_vacant', false)
            ->orderBy('office_department')
            ->orderBy('last_name');

        if ($statusFilter === 'active')       $query->whereNull('nature_of_separation');
        elseif ($statusFilter === 'inactive') $query->whereNotNull('nature_of_separation');

        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('last_name',             'like', "%{$term}%")
                  ->orWhere('first_name',           'like', "%{$term}%")
                  ->orWhere('position_title',       'like', "%{$term}%")
                  ->orWhere('item_no_new',                 'like', "%{$term}%")
                  ->orWhere('office_department',  'like', "%{$term}%");
            });
        }
        if ($request->filled('office')) {
            $query->where('office_department', 'like', '%' . $request->input('office') . '%');
        }
        if ($request->filled('sex')) {
            $query->where('sex', $request->input('sex'));
        }

        // Use array instead of Eloquent collection — much lighter in memory
        $records = $query->get()->toArray();
        $filters = $request->only(['search', 'office', 'sex', 'vacant']);

        $pdf = Pdf::loadView('exports.permanent-pdf', compact('records', 'filters', 'columns'))
            ->setPaper('a4', 'landscape')
            ->setOption('defaultFont', 'DejaVu Sans')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('chunkSize', 512);

        $filename = 'permanent-employees-' . now()->format('Ymd-His') . '.pdf';

        if (Auth::check()) {
            ActivityLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'Exported Data',
                'description' => 'Exported Permanent Employees data to PDF',
            ]);
        }

        return $pdf->download($filename);
    }

    // ── Delete All (Super Admin Only) ─────────────────────────────────────────

    /**
     * Permanently delete ALL Permanent (P, CT, E) PlantillaRecord entries.
     * Restricted to Super Admin only. For data-reset purposes.
     */
    public function deleteAll(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $count = PlantillaRecord::whereIn('employment_status', self::STATUSES)->count();

        PlantillaRecord::whereIn('employment_status', self::STATUSES)->delete();

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'Deleted All Permanent Data',
            'description' => "Permanently wiped {$count} Permanent/CT/Elected PlantillaRecord entries.",
        ]);

        return redirect(session('last_index_url', route('permanent.index')))
            ->with('success', "All {$count} Permanent/CT/Elected record(s) have been permanently deleted.");
    }
}

