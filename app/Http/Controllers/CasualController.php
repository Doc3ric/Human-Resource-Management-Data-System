<?php

namespace App\Http\Controllers;

use App\Models\CasualEmployee;
use App\Models\ActivityLog;
use App\Imports\CasualImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class CasualController extends Controller
{
    /**
     * List all Casual employees with search/filter.
     */
    public function index(Request $request)
    {
        session(['last_index_url' => request()->fullUrl()]);


        $query = CasualEmployee::query()->where('is_vacant', false)->whereNull('nature_of_separation')->orderBy('office_department')->orderBy('last_name');

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }
        if ($request->filled('office')) {
            $query->byOffice($request->input('office'));
        }
        if ($request->filled('sex')) {
            $query->where('sex', strtoupper($request->input('sex')));
        }
        if ($request->filled('position')) {
            $query->where('position_title', $request->input('position'));
        }
        if ($request->filled('detail')) {
            $query->where('remarks_annotation', 'like', '%' . $request->input('detail') . '%');
        }

        $total = (clone $query)->count();
        $maleCount = (clone $query)->where('sex', 'M')->count();
        $femaleCount = (clone $query)->where('sex', 'F')->count();
        $vacantCount = (clone $query)->where('is_vacant', true)->count();
        
        $activeCount = (clone $query)->where('is_vacant', false)
            ->whereNotNull('position_title')
            ->where(function($q) {
                $q->where(function($sub) { $sub->whereNotNull('last_name')->where('last_name', '!=', ''); })
                  ->orWhere(function($sub) { $sub->whereNotNull('first_name')->where('first_name', '!=', ''); });
            })->count();

        $byOffice = (clone $query)->reorder()->select('office_department', \DB::raw('count(*) as count'))
            ->groupBy('office_department')
            ->pluck('count', 'office_department')
            ->sortKeys();
        $perPageInput = $request->input('per_page', 50);
        $perPage = $perPageInput === 'all' ? max(1, $total) : (int) $perPageInput;
        $records = $query->paginate($perPage)->withQueryString();

        // Filter options
        $offices    = CasualEmployee::distinct()->orderBy('office_department')->pluck('office_department')->filter()->values();
        $detailList = CasualEmployee::where('is_vacant', false)->distinct()->orderBy('remarks_annotation')->pluck('remarks_annotation')->filter()->values();
        $positions  = CasualEmployee::distinct()->pluck('position_title')->filter()->map(fn($v) => trim($v))->unique()->sortBy(fn($v) => strtolower($v))->values();

        return view('casual.index', compact(
            'records',
            'total',
            'maleCount',
            'femaleCount',
            'vacantCount',
            'activeCount',
            'byOffice',
            'offices',
            'detailList',
            'positions'
        ));
    }

    /**
     * Show form to create a new Casual record.
     */
    public function create()
    {
        $offices = CasualEmployee::distinct()->orderBy('office_department')->pluck('office_department')->filter()->values();
        $positions = CasualEmployee::distinct()->pluck('position_title')
            ->filter()->map(fn($v) => trim($v))->unique()->sortBy(fn($v) => strtolower($v))->values();
        $districts = CasualEmployee::distinct()->orderBy('legislative_district')->pluck('legislative_district')->filter()->values();
        return view('casual.create', compact('offices', 'positions', 'districts'));
    }

    /**
     * Store a new Casual record.
     */
    public function store(Request $request)
    {
        $validated = $this->validateRecord($request);

        // Auto-generate employee code if not provided
        if (empty($validated['employee_code'])) {
            $validated['employee_code'] = CasualEmployee::generateEmployeeCode(
                $validated['first_name']  ?? null,
                $validated['date_of_birth'] ?? null,
                null,
                $validated['middle_name'] ?? null,
                $validated['last_name']   ?? null
            );
        }

        $casual = CasualEmployee::create($validated);

        // Sync to ALL DATA
        $this->syncToAllData($casual);

        if (Auth::check()) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Created Record',
                'description' => 'Created Casual record for "' . $casual->full_name . '"',
            ]);
        }

        return redirect(session('last_index_url', route('casual.index')))
            ->with('success', 'Casual record created successfully.');
    }

    /**
     * Show form to edit an existing Casual record.
     */
    public function edit(CasualEmployee $casual)
    {
        $offices = CasualEmployee::distinct()->orderBy('office_department')->pluck('office_department')->filter()->values();
        $positions = CasualEmployee::distinct()->pluck('position_title')
            ->filter()->map(fn($v) => trim($v))->unique()->sortBy(fn($v) => strtolower($v))->values();
        $districts = CasualEmployee::distinct()->orderBy('legislative_district')->pluck('legislative_district')->filter()->values();
        return view('casual.edit', compact('casual', 'offices', 'positions', 'districts'));
    }

    /**
     * Update a Casual record.
     */
    public function update(Request $request, CasualEmployee $casual)
    {
        $validated = $this->validateRecord($request, $casual->id);

        // Auto-generate employee code if cleared
        if (empty($validated['employee_code'])) {
            $validated['employee_code'] = CasualEmployee::generateEmployeeCode(
                $validated['first_name']  ?? null,
                $validated['date_of_birth'] ?? null,
                null,
                $validated['middle_name'] ?? null,
                $validated['last_name']   ?? null
            );
        }

        $casual->update($validated);

        // Sync changes back to the PlantillaRecord (All Data) entry
        $this->resyncToAllData($casual);

        if (Auth::check()) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Modified Data',
                'description' => 'Updated Casual record for "' . $casual->full_name . '"',
            ]);
        }

        return redirect(session('last_index_url', route('casual.index')))
            ->with('success', 'Casual record updated successfully.');
    }

    /**
     * Soft-delete a Casual record.
     */
    public function destroy(CasualEmployee $casual)
    {
        $name = $casual->full_name;
        $casual->delete();

        if (Auth::check()) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Deleted Record',
                'description' => 'Deleted Casual record for "' . $name . '"',
            ]);
        }

        return redirect(session('last_index_url', route('casual.index')))
            ->with('success', 'Casual record deleted successfully.');
    }

    /**
     * Handle Excel / CSV import.
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new CasualImport();
        Excel::import($import, $request->file('import_file'));

        if (Auth::check() && $import->imported > 0) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action'  => 'Imported Casual Data',
                'description' => json_encode([
                    'mode'              => 'upsert',
                    'created'           => $import->imported,
                    'skipped'           => $import->skipped,
                    // Exact IDs for reliable undo:
                    'casual_ids'        => $import->createdCasualIds,
                    'plantilla_ids'     => $import->createdPlantillaIds,
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
        return redirect(session('last_index_url', route('casual.index')))->with($type, $message);
    }

    /**
     * Download a blank Casual Excel template.
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Casual Template');

        // Title row
        $sheet->mergeCells('A1:V1');
        $sheet->setCellValue('A1', 'CASUAL EMPLOYEES IMPORT TEMPLATE — CSC Plantilla System');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(24);

        // Column headers (row 2)
        $headers = [
            'A2' => 'OFFICE',
            'B2' => 'ITEM NO. (OLD)',
            'C2' => 'ITEM NO. (NEW)',
            'D2' => 'POSITION TITLE',
            'E2' => 'LAST NAME',
            'F2' => 'FIRST NAME',
            'G2' => 'MIDDLE NAME',
            'H2' => 'NAME EXT (Jr./Sr.)',
            'I2' => 'VACANT? (Y=Yes)',
            'J2' => 'SG (CURRENT)',
            'K2' => 'STEP (CURRENT)',
            'L2' => 'ANNUAL SALARY (CURRENT)',
            'M2' => 'SG (PROPOSED)',
            'N2' => 'STEP (PROPOSED)',
            'O2' => 'ANNUAL SALARY (PROPOSED)',
            'P2' => 'INCREASE/DECREASE',
            'Q2' => 'PREVIOUS RATE (Monthly)',
            'R2' => 'CURRENT RATE (Monthly)',
            'S2' => 'SEX (M/F)',
            'T2' => 'BIRTHDATE (YYYY-MM-DD)',
            'U2' => 'FIRST DAY OF SERVICE',
            'V2' => 'ELIGIBILITY',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $sheet->getStyle('A2:V2')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'AAAAAA']]],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(32);

        // Sample data row
        $sample = [
            'A3' => 'PROVINCIAL VICE GOVERNOR\'S OFFICE',
            'B3' => '1',
            'C3' => '1',
            'D3' => 'Administrative Aide I (Utility Worker I)',
            'E3' => 'Bongcales',
            'F3' => 'Karola Jun',
            'G3' => 'T.',
            'H3' => '',
            'I3' => 'N',
            'J3' => '1',
            'K3' => '1',
            'L3' => '168732.00',
            'M3' => '1',
            'N3' => '1',
            'O3' => '168732.00',
            'P3' => '0',
            'Q3' => '14061.00',
            'R3' => '14061.00',
            'S3' => 'F',
            'T3' => '1990-05-20',
            'U3' => '2015-01-02',
            'V3' => 'No Eligibility',
        ];
        foreach ($sample as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }
        $sheet->getStyle('A3:V3')->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF8E1']],
            'font' => ['size' => 9, 'italic' => true, 'color' => ['rgb' => '555555']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
        ]);

        // Note row
        $sheet->mergeCells('A4:V4');
        $sheet->setCellValue('A4', '⚠ Delete row 3 (sample) before uploading real data. For VACANT positions: enter Y in column I and leave name columns empty. Dates: YYYY-MM-DD format.');
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '888888'], 'size' => 8],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFDE7']],
            'alignment' => ['wrapText' => true],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(28);

        // Auto-width
        foreach (range('A', 'V') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->freezePane('A3');

        $writer = new Xlsx($spreadsheet);
        $filename = 'Casual_Import_Template_' . now()->format('Y') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    /**
     * Export Casual inventory as PDF.
     */
    public function exportPdf(Request $request)
    {
        $query = CasualEmployee::query()->orderBy('office_department')->orderBy('last_name');

        $statusFilter = $request->input('status_filter', 'active');
        if ($statusFilter === 'active')       $query->whereNull('nature_of_separation');
        elseif ($statusFilter === 'inactive') $query->whereNotNull('nature_of_separation');

        if ($request->filled('search'))
            $query->search($request->input('search'));
        if ($request->filled('office'))
            $query->byOffice($request->input('office'));
        if ($request->filled('sex'))
            $query->bySex($request->input('sex'));
        if ($request->filled('vacant'))
            $query->where('is_vacant', $request->input('vacant') === 'vacant');

        $columns = $request->input('columns', []);
        $records = $query->get();

        $pdf = Pdf::loadView('exports.casual-pdf', compact('records', 'columns'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('Casual_Inventory_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export Casual inventory as Excel.
     */
    public function exportExcel(Request $request)
    {
        $query = CasualEmployee::query()->orderBy('office_department')->orderBy('last_name');

        $statusFilter = $request->input('status_filter', 'active');
        if ($statusFilter === 'active')       $query->whereNull('nature_of_separation');
        elseif ($statusFilter === 'inactive') $query->whereNotNull('nature_of_separation');

        if ($request->filled('search'))
            $query->search($request->input('search'));
        if ($request->filled('office'))
            $query->byOffice($request->input('office'));
        if ($request->filled('sex'))
            $query->bySex($request->input('sex'));
        if ($request->filled('vacant'))
            $query->where('is_vacant', $request->input('vacant') === 'vacant');

        $columns = $request->input('columns', []);
        $records = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Casual Employees');

        // Headers
        $allCols = [
            'office' => 'OFFICE',
            'item_no_old' => 'ITEM NO. (OLD)',
            'item_no_new' => 'ITEM NO. (NEW)',
            'position_title' => 'POSITION TITLE',
            'is_vacant' => 'VACANT?',
            'last_name' => 'LAST NAME',
            'first_name' => 'FIRST NAME',
            'middle_name' => 'MIDDLE NAME',
            'name_extension' => 'SUFFIX',
            'legislative_district' => 'LEGISLATIVE DISTRICT',
            'sg_current' => 'SALARY GRADE (CURRENT)',
            'step_current' => 'STEP (CURRENT)',
            'salary_current' => 'ANNUAL SALARY (CURRENT)',
            'sg_proposed' => 'SALARY GRADE (PROPOSED)',
            'step_proposed' => 'STEP (PROPOSED)',
            'salary_proposed' => 'ANNUAL SALARY (PROPOSED)',
            'increase_decrease' => 'INCREASE / DECREASE',
            'previous_rate' => 'PREVIOUS RATE',
            'current_rate' => 'CURRENT RATE (MONTHLY)',
            'sex' => 'SEX',
            'civil_status' => 'CIVIL STATUS',
            'date_of_birth' => 'DATE OF BIRTH',
            'first_day_of_service' => 'FIRST DAY OF SERVICE',
            'eligibility' => 'ELIGIBILITY',
            'annotation' => 'ANNOTATION',
            'employee_code' => 'EMPLOYEE CODE',
            'address' => 'ADDRESS',
            'solo_parent' => 'SOLO PARENT',
            'ip_community_membership' => 'IP COMMUNITY MEMBERSHIP'
        ];

        $cols = ['#'];
        $exportKeys = [];
        foreach ($allCols as $key => $label) {
            if (empty($columns) || in_array($key, $columns)) {
                $cols[] = $label;
                $exportKeys[] = $key;
            }
        }

        foreach ($cols as $i => $label) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . '1';
            $sheet->setCellValue($cell, $label);
        }
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($cols));
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Data rows
        foreach ($records as $i => $r) {
            $row = $i + 2;
            $sheet->setCellValueByColumnAndRow(1, $row, $i + 1);
            
            $colIndex = 2;
            foreach ($exportKeys as $key) {
                $val = '';
                switch ($key) {
                    case 'office': $val = $r->office; break;
                    case 'item_old': $val = $r->item_no_new_no_old; break;
                    case 'item_new': $val = $r->item_no_new_no_new; break;
                    case 'position_title': $val = $r->position_title; break;
                    case 'is_vacant': $val = $r->is_vacant ? 'Y' : 'N'; break;
                    case 'last_name': $val = strtoupper($r->last_name ?? ''); break;
                    case 'first_name': $val = $r->first_name; break;
                    case 'middle_name': $val = $r->middle_name; break;
                    case 'name_extension': $val = $r->name_extension; break;
                    case 'legislative_district': $val = $r->legislative_district; break;
                    case 'sex': $val = $r->sex; break;
                    case 'civil_status': $val = $r->civil_status; break;
                    case 'date_of_birth': $val = $r->date_of_birth?->format('Y-m-d'); break;
                    case 'first_day_of_service': $val = $r->first_day_of_service?->format('Y-m-d'); break;
                    case 'eligibility': $val = $r->eligibility; break;
                    case 'address': $val = $r->address; break;
                    case 'solo_parent': $val = $r->solo_parent ? 'Y' : 'N'; break;
                    case 'ip_community_membership': $val = $r->ip_community_membership; break;
                    case 'sg_step_current': $val = ($r->sg_current ? "SG-{$r->sg_current}/Step {$r->step_current}" : ''); break;
                    case 'annual_salary_current': $val = $r->salary_current; break;
                    case 'sg_step_proposed': $val = ($r->sg_proposed ? "SG-{$r->sg_proposed}/Step {$r->step_proposed}" : ''); break;
                    case 'annual_salary_proposed': $val = $r->salary_proposed; break;
                    case 'increase_decrease': $val = $r->increase_decrease; break;
                    case 'previous_rate': $val = $r->previous_rate; break;
                    case 'monthly_rate': $val = $r->current_rate; break;
                    case 'annotation': $val = $r->remarks_annotation; break;
                    case 'employee_code': $val = $r->employee_code; break;
                }
                $sheet->setCellValueByColumnAndRow($colIndex, $row, $val);
                $colIndex++;
            }
        }

        $lastColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($lastCol);
        for ($col = 1; $col <= $lastColIndex; $col++) {
            $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'Casual_Inventory_' . now()->format('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $writer->save('php://output');
        exit;
    }

    /**
     * Import history.
     */
    public function importHistory()
    {
        $logs = ActivityLog::whereIn('action', ['Imported Casual Data'])
            ->latest()
            ->paginate(20);
        return view('casual.history', compact('logs'));
    }

    /**
     * Undo a specific Casual import.
     * Uses the exact IDs stored in the activity log for precise deletion.
     */
    public function undoImport($id)
    {
        $log  = ActivityLog::where('action', 'Imported Casual Data')->findOrFail($id);
        $info = json_decode($log->description, true) ?? [];

        // ── Use stored IDs if available (new reliable method) ──────────────
        $casualIds   = $info['casual_ids']    ?? [];
        $plantillaIds = $info['plantilla_ids'] ?? [];

        if (!empty($casualIds)) {
            // Exact ID-based deletion
            $deleted   = CasualEmployee::whereIn('id', $casualIds)->delete();
            $deletedPr = !empty($plantillaIds)
                ? \App\Models\PlantillaRecord::whereIn('id', $plantillaIds)->delete()
                : 0;

            $info['deleted_via_undo'] = ($info['deleted_via_undo'] ?? 0) + $deleted;
            $info['casual_ids']       = [];
            $info['plantilla_ids']    = [];
            $log->update(['description' => json_encode($info)]);

            ActivityLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'Undid Casual Import',
                'description' => "Deleted {$deleted} Casual records (+ {$deletedPr} All Data entries) from import on "
                    . $log->created_at->format('M d, Y h:i A'),
            ]);

            return back()->with('success',
                "Successfully deleted {$deleted} Casual record(s) and {$deletedPr} All Data entry(ies) created by this import."
            );
        }

        // ── Fallback: legacy imports that have no stored IDs ───────────────
        $createdCount = $info['created'] ?? 0;
        if ($createdCount <= 0) {
            return back()->with('error', 'This import has no records to undo.');
        }

        // Narrow window: log is written right after import finishes
        $startTime = $log->created_at->copy()->subMinutes(10);
        $endTime   = $log->created_at->copy()->addMinutes(2);

        $ids = CasualEmployee::whereBetween('created_at', [$startTime, $endTime])
            ->orderBy('created_at', 'desc')
            ->limit($createdCount)
            ->pluck('id');

        if ($ids->isEmpty()) {
            return back()->with('error', 'Could not find any records created by this import (they may have already been deleted).');
        }

        $deleted   = CasualEmployee::whereIn('id', $ids)->delete();
        $prIds     = \App\Models\PlantillaRecord::where('employment_status', 'Casual')
            ->whereBetween('created_at', [$startTime, $endTime])
            ->orderBy('created_at', 'desc')
            ->limit($createdCount)
            ->pluck('id');
        $deletedPr = \App\Models\PlantillaRecord::whereIn('id', $prIds)->delete();

        if ($deleted > 0) {
            $info['deleted_via_undo'] = ($info['deleted_via_undo'] ?? 0) + $deleted;
            $log->update(['description' => json_encode($info)]);

            ActivityLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'Undid Casual Import',
                'description' => "Deleted {$deleted} records from import on " . $log->created_at->format('M d, Y h:i A'),
            ]);

            return back()->with('success',
                "Successfully deleted {$deleted} Casual record(s) (and {$deletedPr} All Data entries) from this import."
            );
        }

        return back()->with('error', 'Could not find any records created by this import.');
    }

    // ── Delete All (Testing Only) ─────────────────────────────────────────────

    /**
     * Delete ALL casual employee records and their synced All-Data entries.
     * Restricted to Super Admin. For testing purposes only.
     */
    public function deleteAll(Request $request)
    {
        // Hard-gate: super_admin only
        if (!Auth::check() || !Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $casualCount    = CasualEmployee::count();
        $plantillaCount = \App\Models\PlantillaRecord::where('employment_status', 'Casual')->count();

        // Delete synced All Data entries first
        \App\Models\PlantillaRecord::where('employment_status', 'Casual')->delete();

        // Delete all casual employees
        CasualEmployee::query()->delete();

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'Deleted All Casual Data',
            'description' => "Wiped {$casualCount} Casual record(s) and {$plantillaCount} synced All-Data entry(ies).",
        ]);

        return redirect(session('last_index_url', route('casual.index')))
            ->with('success', "All {$casualCount} Casual record(s) and {$plantillaCount} All-Data entry(ies) have been deleted.");
    }

    // ── Private Helpers ───────────────────────────────────────────────────────

    private function syncToAllData(CasualEmployee $casual): void
    {
        \App\Models\PlantillaRecord::create([
            'office_department' => $casual->office,
            'last_name' => $casual->last_name,
            'first_name' => $casual->first_name,
            'middle_name' => $casual->middle_name,
            'position_title' => $casual->position_title,
            'salary_grade' => $casual->sg_current,
            'step' => $casual->step_current,
            'authorized_annual_salary' => $casual->salary_current,
            'base_salary_amount' => $casual->salary_current,
            'sex' => $casual->sex,
            'date_of_birth' => $casual->date_of_birth?->format('Y-m-d'),
            'date_original_appointment' => $casual->first_day_of_service?->format('Y-m-d'),
            'civil_service_eligibility' => $casual->eligibility,
            'employment_status' => 'Casual',
            'is_vacant' => $casual->is_vacant,
            'item_no_new' => $casual->item_no_new_no_new ?? $casual->item_no_new_no_old,
        ]);
    }

    /**
     * Update the linked PlantillaRecord (All Data) when a CasualEmployee is edited.
     * Matches by name + employment_status so we keep the existing row up-to-date.
     */
    private function resyncToAllData(CasualEmployee $casual): void
    {
        $pr = \App\Models\PlantillaRecord::where('employment_status', 'Casual')
            ->where('first_name', $casual->first_name)
            ->where('last_name', $casual->last_name)
            ->first();

        if ($pr) {
            $pr->update([
                'office_department'      => $casual->office,
                'position_title'           => $casual->position_title,
                'salary_grade'             => $casual->sg_current,
                'step'                     => $casual->step_current,
                'authorized_annual_salary' => $casual->salary_current,
                'base_salary_amount'     => $casual->salary_current,
                'sex'                      => $casual->sex,
                'date_of_birth'            => $casual->date_of_birth?->format('Y-m-d'),
                'date_original_appointment'=> $casual->first_day_of_service?->format('Y-m-d'),
                'civil_service_eligibility'=> $casual->eligibility,
                'is_vacant'                => $casual->is_vacant,
                'item_no_new'                     => $casual->item_no_new_no_new ?? $casual->item_no_new_no_old,
            ]);
        }
    }


    private function validateRecord(Request $request, ?int $exceptId = null): array
    {
        return $request->validate([
            'office' => 'nullable|string|max:200',
            'item_no_old' => 'nullable|string|max:20',
            'item_no_new' => 'nullable|string|max:20',
            'position_title' => 'required|string|max:200',
            'is_vacant' => 'boolean',
            'last_name' => 'nullable|string|max:100',
            'first_name' => 'nullable|string|max:100',
            'middle_name'    => 'nullable|string|max:255',
            'name_extension' => 'nullable|string|max:20',
            'civil_status'   => 'nullable|string|max:50',
            'legislative_district' => 'nullable|string|max:100',
            'sg_current' => 'nullable|integer|min:1|max:33',
            'step_current' => 'nullable|integer|min:1|max:8',
            'salary_current' => 'nullable|numeric|min:0',
            'sg_proposed' => 'nullable|integer|min:1|max:33',
            'step_proposed' => 'nullable|integer|min:1|max:8',
            'salary_proposed' => 'nullable|numeric|min:0',
            'increase_decrease' => 'nullable|numeric',
            'previous_rate' => 'nullable|numeric|min:0',
            'current_rate' => 'nullable|numeric|min:0',
            'sex' => 'nullable|in:M,F',
            'date_of_birth' => 'nullable|date',
            'first_day_of_service' => 'nullable|date',
            'eligibility' => 'nullable|string|max:200',
            'address' => 'nullable|string|max:500',
            'solo_parent' => 'boolean',
            'ip_community_membership' => 'nullable|string|max:200',
            'annotation' => 'nullable|string|max:1000',
            'nature_of_separation' => 'nullable|string|max:100',
            'date_separated'       => 'nullable|date',
            'employee_code' => 'nullable|string|max:20|unique:plantilla_records,employee_code' . ($exceptId ? ",{$exceptId}" : ''),
        ]);
    }
}
