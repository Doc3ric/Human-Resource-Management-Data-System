<?php

namespace App\Http\Controllers;

use App\Models\PlantillaRecord;
use App\Exports\PermanentExport;
use App\Imports\PermanentImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ActivityLog;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

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


        $baseQuery = PlantillaRecord::whereIn('employment_status', self::STATUSES)
            ->whereNull('nature_of_separation');

        if ($request->filled('search')) {
            $term = $request->input('search');
            $baseQuery->where(function ($q) use ($term) {
                $q->where('last_name',        'like', "%{$term}%")
                  ->orWhere('first_name',     'like', "%{$term}%")
                  ->orWhere('position_title', 'like', "%{$term}%")
                  ->orWhere('item_no_new',           'like', "%{$term}%")
                  ->orWhere('office_department', 'like', "%{$term}%");
            });
        }

        if ($request->filled('office_department')) {
            $baseQuery->where('office_department', $request->input('office_department'));
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            $baseQuery->where('employment_status', $status);
        }

        if ($request->filled('position')) {
            $baseQuery->where('position_title', $request->input('position'));
        }

        if ($request->filled('office')) {
            $baseQuery->where('detailed_unit', $request->input('office'));
        }

        if ($request->filled('sex')) {
            $baseQuery->where('sex', $request->input('sex'));
        }

        if ($request->filled('vacancy_status')) {
            if ($request->input('vacancy_status') === 'vacant') {
                $baseQuery->where('is_vacant', true);
            } elseif ($request->input('vacancy_status') === 'filled') {
                $baseQuery->where('is_vacant', false);
            }
        }

        $vacantCount = (clone $baseQuery)->where('is_vacant', true)->count();

        $query = (clone $baseQuery)
            ->orderBy('office_department')
            ->orderBy('last_name');

        // Enhancement Spec Sec. 1 — sort is independent of (and never resets) the filters above.
        $sortableColumns = ['last_name', 'first_name', 'office_department', 'detailed_unit', 'position_title', 'sex', 'employment_status'];
        if ($request->filled('sort') && in_array($request->input('sort'), $sortableColumns, true)) {
            $direction = $request->input('direction') === 'desc' ? 'desc' : 'asc';
            $query->reorder()->orderBy($request->input('sort'), $direction);
        }

        $total       = (clone $query)->count();
        $maleCount   = (clone $query)->where('sex', 'M')->count();
        $femaleCount = (clone $query)->where('sex', 'F')->count();
        
        $perPage = $request->input('per_page', 50);
        $records = $query->paginate($perPage)->withQueryString();

        $offices = PlantillaRecord::whereIn('employment_status', self::STATUSES)
            ->distinct()->orderBy('office_department')
            ->pluck('office_department')->filter()->values();
            
        $detailedUnits = PlantillaRecord::whereIn('employment_status', self::STATUSES)
            ->distinct()->orderBy('detailed_unit')
            ->pluck('detailed_unit')->filter()->values();
            
        $positions = PlantillaRecord::whereIn('employment_status', self::STATUSES)
            ->distinct()->pluck('position_title')->filter()->map(fn($v) => trim($v))->unique()->sortBy(fn($v) => strtolower($v))->values();

        return view('permanent.index', compact(
            'records',
            'total',
            'maleCount',
            'femaleCount',
            'vacantCount',
            'offices',
            'detailedUnits',
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
            'last_name', 'first_name', 'middle_name', 'name_extension', 'sex',
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

    // ── Create / Store ────────────────────────────────────────────────────────

    public function create()
    {
        $offices = PlantillaRecord::whereIn('employment_status', self::STATUSES)
            ->distinct()->orderBy('office_department')
            ->pluck('office_department')->filter()->values();

        $positions = PlantillaRecord::whereIn('employment_status', self::STATUSES)
            ->distinct()->pluck('position_title')->filter()
            ->map(fn ($v) => trim($v))->unique()->sortBy(fn ($v) => strtolower($v))->values();

        return view('permanent.create', compact('offices', 'positions'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateRecord($request);

        if (empty($validated['employee_code']) && !($validated['is_vacant'] ?? false)) {
            $validated['employee_code'] = PlantillaRecord::generateEmployeeCode(
                $validated['first_name']  ?? null,
                $validated['date_of_birth'] ?? null,
                null,
                $validated['middle_name'] ?? null,
                $validated['last_name']   ?? null
            );
        }

        $record = PlantillaRecord::create($validated);

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'Created Record',
            'description' => 'Created Permanent/CT/Elected record for item ' . ($record->item_no_new ?? $record->id),
        ]);

        return redirect(session('last_index_url', route('permanent.index')))
            ->with('success', 'Record created successfully.');
    }

    private function validateRecord(Request $request): array
    {
        return $request->validate([
            'employment_status' => 'required|in:P,CT,E',
            'office_department' => 'nullable|string|max:200',
            'item_no_old' => 'nullable|string|max:20',
            'item_no_new' => 'nullable|string|max:20',
            'position_title' => 'required|string|max:200',
            'is_vacant' => 'boolean',
            'last_name' => 'nullable|string|max:100',
            'first_name' => 'nullable|string|max:100',
            'middle_name' => 'nullable|string|max:255',
            'name_extension' => 'nullable|string|max:20',
            'civil_status' => 'nullable|string|max:50',
            'legislative_district' => 'nullable|string|max:100',
            'salary_grade' => 'nullable|integer|min:1|max:33',
            'step' => 'nullable|integer|min:1|max:8',
            'authorized_annual_salary' => 'nullable|numeric|min:0',
            'sg_proposed' => 'nullable|integer|min:1|max:33',
            'step_proposed' => 'nullable|integer|min:1|max:8',
            'salary_proposed' => 'nullable|numeric|min:0',
            'increase_decrease' => 'nullable|numeric',
            'previous_rate' => 'nullable|numeric|min:0',
            'base_salary_amount' => 'nullable|numeric|min:0',
            'sex' => 'nullable|in:M,F',
            'date_of_birth' => 'nullable|date',
            'first_day_of_service' => 'nullable|date',
            'civil_service_eligibility' => 'nullable|string|max:200',
            'address' => 'nullable|string|max:500',
            'solo_parent' => 'boolean',
            'ip_community_membership' => 'nullable|string|max:200',
            'remarks_annotation' => 'nullable|string|max:1000',
            'nature_of_separation' => 'nullable|string|max:100',
            'date_separated' => 'nullable|date',
            'employee_code' => 'nullable|string|max:20|unique:plantilla_records,employee_code',
        ]);
    }

    // ── Excel Import ──────────────────────────────────────────────────────────

    public function importExcel(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new PermanentImport();
        Excel::import($import, $request->file('import_file'));

        if (Auth::check() && $import->imported > 0) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action'  => 'Imported Permanent Data',
                'description' => json_encode([
                    'mode'       => 'upsert',
                    'created'    => $import->imported,
                    'skipped'    => $import->skipped,
                    'created_ids' => $import->createdIds,
                ]),
            ]);
        }

        $message = "Successfully imported {$import->imported} record(s).";
        if ($import->skipped > 0) {
            $message .= " {$import->skipped} row(s) were skipped (empty or missing position title).";
        }
        if (!empty($import->errors)) {
            $message .= ' Some rows had errors: ' . implode(' | ', array_slice($import->errors, 0, 3));
        }

        $type = empty($import->errors) ? 'success' : 'error';
        return redirect(session('last_index_url', route('permanent.index')))->with($type, $message);
    }

    /**
     * Download a blank Permanent/CT/Elected Excel import template.
     * Same 22-column layout as the Casual template, plus an Employment
     * Status column (P/CT/E) since this module spans three statuses.
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Permanent Template');

        $sheet->mergeCells('A1:W1');
        $sheet->setCellValue('A1', 'PERMANENT / CO-TERMINOUS / ELECTED IMPORT TEMPLATE — CSC Plantilla System');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(24);

        $headers = [
            'A2' => 'EMPLOYMENT STATUS (P/CT/E)',
            'B2' => 'OFFICE',
            'C2' => 'ITEM NO. (OLD)',
            'D2' => 'ITEM NO. (NEW)',
            'E2' => 'POSITION TITLE',
            'F2' => 'LAST NAME',
            'G2' => 'FIRST NAME',
            'H2' => 'MIDDLE NAME',
            'I2' => 'NAME EXT (Jr./Sr.)',
            'J2' => 'VACANT? (Y=Yes)',
            'K2' => 'SG (CURRENT)',
            'L2' => 'STEP (CURRENT)',
            'M2' => 'ANNUAL SALARY (CURRENT)',
            'N2' => 'SG (PROPOSED)',
            'O2' => 'STEP (PROPOSED)',
            'P2' => 'ANNUAL SALARY (PROPOSED)',
            'Q2' => 'INCREASE/DECREASE',
            'R2' => 'PREVIOUS RATE (Monthly)',
            'S2' => 'CURRENT RATE (Monthly)',
            'T2' => 'SEX (M/F)',
            'U2' => 'BIRTHDATE (YYYY-MM-DD)',
            'V2' => 'FIRST DAY OF SERVICE',
            'W2' => 'ELIGIBILITY',
        ];
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }
        $sheet->getStyle('A2:W2')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'AAAAAA']]],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(32);

        $sample = [
            'A3' => 'P', 'B3' => 'PROVINCIAL GOVERNOR\'S OFFICE - MANAGEMENT AND STAFF',
            'C3' => '1', 'D3' => '1', 'E3' => 'Executive Assistant III',
            'F3' => 'Cruz', 'G3' => 'Cornelio Melan', 'H3' => 'L.', 'I3' => '',
            'J3' => 'N', 'K3' => '20', 'L3' => '1', 'M3' => '755604.00',
            'N3' => '20', 'O3' => '1', 'P3' => '792624.00', 'Q3' => '15425.00',
            'R3' => '62967.00', 'S3' => '66052.00', 'T3' => 'M', 'U3' => '1990-05-20',
            'V3' => '2015-01-02', 'W3' => 'CS Professional',
        ];
        foreach ($sample as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }
        $sheet->getStyle('A3:W3')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF8E1']],
            'font' => ['size' => 9, 'italic' => true, 'color' => ['rgb' => '555555']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
        ]);

        $sheet->mergeCells('A4:W4');
        $sheet->setCellValue('A4', '⚠ Delete row 3 (sample) before uploading real data. For VACANT positions: enter Y in column J and leave name columns empty. Dates: YYYY-MM-DD format.');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '888888'], 'size' => 8],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFDE7']],
            'alignment' => ['wrapText' => true],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(28);

        foreach (range('A', 'W') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->freezePane('A3');

        $writer = new Xlsx($spreadsheet);
        $filename = 'Permanent_Import_Template_' . now()->format('Y') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    /**
     * Import history.
     */
    public function importHistory()
    {
        $logs = ActivityLog::whereIn('action', ['Imported Permanent Data'])
            ->latest()
            ->paginate(20);
        return view('permanent.history', compact('logs'));
    }

    /**
     * Undo a specific Permanent import.
     */
    public function undoImport($id)
    {
        $log  = ActivityLog::where('action', 'Imported Permanent Data')->findOrFail($id);
        $info = json_decode($log->description, true) ?? [];

        $ids = $info['created_ids'] ?? [];
        if (empty($ids)) {
            return back()->with('error', 'This import has no records to undo.');
        }

        $deleted = PlantillaRecord::whereIn('id', $ids)->delete();

        $info['deleted_via_undo'] = ($info['deleted_via_undo'] ?? 0) + $deleted;
        $info['created_ids'] = [];
        $log->update(['description' => json_encode($info)]);

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'Undid Permanent Import',
            'description' => "Deleted {$deleted} Permanent record(s) from import on " . $log->created_at->format('M d, Y h:i A'),
        ]);

        return back()->with('success', "Successfully deleted {$deleted} record(s) created by this import.");
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

