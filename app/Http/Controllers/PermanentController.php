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
        $query = PlantillaRecord::whereIn('employment_status', self::STATUSES)
            ->orderBy('organizational_unit')
            ->orderBy('last_name');

        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('last_name',        'like', "%{$term}%")
                  ->orWhere('first_name',     'like', "%{$term}%")
                  ->orWhere('position_title', 'like', "%{$term}%")
                  ->orWhere('item',           'like', "%{$term}%")
                  ->orWhere('organizational_unit', 'like', "%{$term}%");
            });
        }

        if ($request->filled('office')) {
            $query->where('organizational_unit', 'like', '%' . $request->input('office') . '%');
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            $query->where('employment_status', $status);
        }

        if ($request->filled('sex')) {
            $query->where('sex', $request->input('sex'));
        }

        if ($request->filled('vacant')) {
            $query->where('is_vacant', $request->input('vacant') === 'vacant');
        }

        $total       = (clone $query)->count();
        $maleCount   = (clone $query)->where('sex', 'M')->count();
        $femaleCount = (clone $query)->where('sex', 'F')->count();
        $vacantCount = (clone $query)->where('is_vacant', true)->count();
        
        $records = $query->paginate(50)->withQueryString();

        $offices = PlantillaRecord::whereIn('employment_status', self::STATUSES)
            ->distinct()->orderBy('organizational_unit')
            ->pluck('organizational_unit')->filter()->values();

        return view('permanent.index', compact(
            'records',
            'total',
            'maleCount',
            'femaleCount',
            'vacantCount',
            'offices'
        ));
    }

    // ── Excel Export ──────────────────────────────────────────────────────────

    public function exportExcel(Request $request)
    {
        // Force status filter to only permanent statuses
        $filters = $request->only(['search', 'office', 'sex', 'vacant']);
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
            'id', 'organizational_unit', 'item', 'position_title',
            'salary_grade', 'step', 'actual_annual_salary',
            'last_name', 'first_name', 'middle_name', 'sex',
            'date_of_birth', 'tin', 'date_original_appointment',
            'date_last_promotion', 'civil_service_eligibility',
            'employment_status', 'is_vacant',
        ];

        $query = PlantillaRecord::select($dbColumns)
            ->whereIn('employment_status', self::STATUSES)
            ->orderBy('organizational_unit')
            ->orderBy('last_name');

        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('last_name',             'like', "%{$term}%")
                  ->orWhere('first_name',           'like', "%{$term}%")
                  ->orWhere('position_title',       'like', "%{$term}%")
                  ->orWhere('item',                 'like', "%{$term}%")
                  ->orWhere('organizational_unit',  'like', "%{$term}%");
            });
        }
        if ($request->filled('office')) {
            $query->where('organizational_unit', 'like', '%' . $request->input('office') . '%');
        }
        if ($request->filled('sex')) {
            $query->where('sex', $request->input('sex'));
        }
        if ($request->filled('vacant')) {
            $query->where('is_vacant', $request->input('vacant') === 'vacant');
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
}

