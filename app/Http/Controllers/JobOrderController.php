<?php

namespace App\Http\Controllers;

use App\Models\JobOrder;
use App\Models\ActivityLog;
use App\Imports\JobOrderImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;

class JobOrderController extends Controller
{
    /**
     * List all Job Order employees with search/filter.
     */
    public function index(Request $request)
    {
        $query = JobOrder::query()->orderBy('charges')->orderBy('last_name');

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }
        if ($request->filled('office')) {
            $query->where('office', $request->input('office'));
        }
        if ($request->filled('charges')) {
            $query->where('charges', $request->input('charges'));
        }
        if ($request->filled('nature_of_work')) {
            $query->where('nature_of_work', 'like', '%' . $request->input('nature_of_work') . '%');
        }
        if ($request->filled('gender')) {
            $query->where('gender', strtoupper($request->input('gender')));
        }

        $records = $query->get();

        // Stats
        $total       = $records->count();
        $maleCount   = $records->where('gender', 'M')->count();
        $femaleCount = $records->where('gender', 'F')->count();

        $byOffice = $records->groupBy('office')
            ->map(fn($g) => $g->count())
            ->sortKeys();

        $byNature = $records->groupBy('nature_of_work')
            ->map(fn($g) => $g->count())
            ->sortKeys();

        // Filter options
        $offices    = JobOrder::distinct()->orderBy('office')->pluck('office')->filter()->values();
        $chargesList = JobOrder::distinct()->orderBy('charges')->pluck('charges')->filter()->values();
        $natures    = JobOrder::distinct()->orderBy('nature_of_work')->pluck('nature_of_work')->filter()->values();

        return view('job-orders.index', compact(
            'records', 'total', 'maleCount', 'femaleCount',
            'byOffice', 'byNature', 'offices', 'chargesList', 'natures'
        ));
    }

    /**
     * Show form to create a new JO record.
     */
    public function create()
    {
        $offices     = JobOrder::distinct()->orderBy('office')->pluck('office')->filter()->values();
        $chargesList = JobOrder::distinct()->orderBy('charges')->pluck('charges')->filter()->values();
        $natures     = JobOrder::distinct()->orderBy('nature_of_work')->pluck('nature_of_work')->filter()->values();
        return view('job-orders.create', compact('offices', 'chargesList', 'natures'));
    }

    /**
     * Store a new JO record.
     */
    public function store(Request $request)
    {
        $validated = $this->validateRecord($request);

        // Auto-generate employee code if not provided
        if (empty($validated['employee_code'])) {
            $validated['employee_code'] = JobOrder::generateEmployeeCode(
                $validated['last_name'] ?? null,
                $validated['birthdate'] ?? null
            );
        }

        $jo = JobOrder::create($validated);

        if (Auth::check()) {
            ActivityLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'Created Record',
                'description' => 'Created Job Order record for "' . $jo->full_name . '"',
            ]);
        }

        return redirect()->route('job-orders.index')
            ->with('success', 'Job Order record created successfully.');
    }

    /**
     * Show form to edit an existing JO record.
     */
    public function edit(JobOrder $jobOrder)
    {
        $jobOrder->load('attachments');
        $offices     = JobOrder::distinct()->orderBy('office')->pluck('office')->filter()->values();
        $chargesList = JobOrder::distinct()->orderBy('charges')->pluck('charges')->filter()->values();
        $natures     = JobOrder::distinct()->orderBy('nature_of_work')->pluck('nature_of_work')->filter()->values();
        return view('job-orders.edit', compact('jobOrder', 'offices', 'chargesList', 'natures'));
    }

    /**
     * Update a JO record.
     */
    public function update(Request $request, JobOrder $jobOrder)
    {
        $validated = $this->validateRecord($request, $jobOrder->id);

        // Auto-generate employee code if cleared
        if (empty($validated['employee_code'])) {
            $validated['employee_code'] = JobOrder::generateEmployeeCode(
                $validated['last_name'] ?? null,
                $validated['birthdate'] ?? null
            );
        }

        $jobOrder->update($validated);

        if (Auth::check()) {
            ActivityLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'Modified Data',
                'description' => 'Updated Job Order record for "' . $jobOrder->full_name . '"',
            ]);
        }

        return redirect()->route('job-orders.index')
            ->with('success', 'Job Order record updated successfully.');
    }

    /**
     * Soft-delete a JO record.
     */
    public function destroy(JobOrder $jobOrder)
    {
        $name = $jobOrder->full_name;
        $jobOrder->delete();

        if (Auth::check()) {
            ActivityLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'Deleted Record',
                'description' => 'Deleted Job Order record for "' . $name . '"',
            ]);
        }

        return redirect()->route('job-orders.index')
            ->with('success', 'Job Order record deleted successfully.');
    }

    /**
     * Handle Excel / CSV import.
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new JobOrderImport();
        Excel::import($import, $request->file('import_file'));

        if (Auth::check() && $import->imported > 0) {
            ActivityLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'Imported JO Data',
                'description' => json_encode([
                    'mode'    => 'upsert',
                    'created' => $import->imported,
                    'updated' => 0,
                    'deleted' => 0,
                    'skipped' => $import->skipped,
                ]),
            ]);
        }

        $message = "Successfully imported {$import->imported} record(s).";
        if ($import->skipped > 0) {
            $message .= " {$import->skipped} row(s) were skipped (empty or missing last name).";
        }
        if (!empty($import->errors)) {
            $message .= ' Some rows had errors: ' . implode(' | ', array_slice($import->errors, 0, 3));
        }

        $type = empty($import->errors) ? 'success' : 'error';
        return redirect()->route('job-orders.index')->with($type, $message);
    }

    /**
     * Download a blank JO Excel template matching the Google Sheets format.
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Inventory');

        // ── Main header row (row 9) ──────────────────────────────────────
        // Columns: A=NO, B=CHARGES, C=FAMILY, D=FIRST, E=MI, F=EXT,
        //          G=POSITION, H=NATURE OF WORK, I=OFFICE, J=RATE/DAY,
        //          K=FIRST DAY OF SERVICE, L=LOS YEARS, M=LOS MONTHS,
        //          N=BIRTHDATE, O=ADDRESS, P=ELIGIBILITY,
        //          Q=GENDER M, R=GENDER F, S=1st LEVEL, T=2nd LEVEL,
        //          U=IP COMMUNITY MEMBERSHIP, V=SOLO PARENT, W=REMARKS
        $headers = [
            'A9' => 'NO.',
            'B9' => 'CHARGES',
            'C9' => 'FAMILY',
            'D9' => 'FIRST',
            'E9' => 'M.I.',
            'F9' => 'EXT',
            'G9' => 'POSITION',
            'H9' => 'NATURE OF WORK',
            'I9' => 'OFFICE',
            'J9' => 'RATE/DAY',
            'K9' => 'FIRST DAY OF SERVICE',
            'L9' => 'LENGTH YRS',
            'M9' => 'LENGTH MOS',
            'N9' => 'BIRTHDATE',
            'O9' => 'ADDRESS',
            'P9' => 'ELIGIBILITY',
            'Q9' => 'GENDER (M)',
            'R9' => 'GENDER (F)',
            'S9' => '1st LEVEL',
            'T9' => '2nd LEVEL',
            'U9' => 'IP COMMUNITY MEMBERSHIP',
            'V9' => 'SOLO PARENT',
            'W9' => 'REMARKS',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Style the header row
        $sheet->getStyle('A9:W9')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'AAAAAA']]],
        ]);
        $sheet->getRowDimension(9)->setRowHeight(30);

        // Title rows (1–8 placeholder)
        $sheet->mergeCells('A1:W1');
        $sheet->setCellValue('A1', 'JO INVENTORY 2026 — Import Template');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(24);

        // Add one sample row
        $sample = ['A10' => '1', 'B10' => 'BEMO', 'C10' => 'DELA CRUZ', 'D10' => 'JUAN',
                   'E10' => 'S.', 'F10' => 'Jr.', 'G10' => 'ADMINISTRATIVE AIDE II',
                   'H10' => 'Clerical services', 'I10' => 'BEMO', 'J10' => '615.00',
                   'K10' => '2024-01-15', 'N10' => '1990-05-20',
                   'O10' => 'Marikina City', 'P10' => 'NO ELIGIBILITY', 'Q10' => '/', 'R10' => ''];
        foreach ($sample as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }
        $sheet->getStyle('A10:W10')->applyFromArray([
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F7FF']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
            'font'    => ['size' => 9, 'italic' => true, 'color' => ['rgb' => '555555']],
        ]);

        // Note row
        $sheet->mergeCells('A11:W11');
        $sheet->setCellValue('A11', '⚠ For GENDER: enter "/" or "x" in the M or F column. For checkboxes (1st/2nd Level, Solo Parent): enter "/" or "x". Dates: YYYY-MM-DD. Delete rows 10–11 before uploading real data.');
        $sheet->getStyle('A11')->applyFromArray([
            'font'      => ['italic' => true, 'color' => ['rgb' => '888888'], 'size' => 8],
            'alignment' => ['wrapText' => true],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFDE7']],
        ]);
        $sheet->getRowDimension(11)->setRowHeight(28);

        // Auto-width columns
        foreach (range('A', 'W') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Freeze pane below header
        $sheet->freezePane('A10');

        $writer = new Xlsx($spreadsheet);
        $filename = 'JO_Import_Template_' . now()->format('Y') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    /**
     * Export JO inventory as PDF.
     */
    public function exportPdf(Request $request)
    {
        $query = JobOrder::query()->orderBy('charges')->orderBy('last_name');

        if ($request->filled('office'))       $query->where('office', $request->input('office'));
        if ($request->filled('charges'))      $query->where('charges', $request->input('charges'));
        if ($request->filled('nature_of_work')) $query->where('nature_of_work', 'like', '%'.$request->input('nature_of_work').'%');
        if ($request->filled('gender'))       $query->where('gender', strtoupper($request->input('gender')));

        $columns = $request->input('columns', []);
        $records = $query->get();

        $pdf = Pdf::loadView('exports.job-orders-pdf', compact('records', 'columns'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('JO_Inventory_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export JO inventory as Excel (.xlsx).
     */
    public function exportExcel(Request $request)
    {
        $query = JobOrder::query()->orderBy('charges')->orderBy('last_name');

        if ($request->filled('search'))         $query->search($request->input('search'));
        if ($request->filled('office'))         $query->where('office', $request->input('office'));
        if ($request->filled('charges'))        $query->where('charges', $request->input('charges'));
        if ($request->filled('nature_of_work')) $query->where('nature_of_work', 'like', '%'.$request->input('nature_of_work').'%');
        if ($request->filled('gender'))         $query->where('gender', strtoupper($request->input('gender')));

        $columns = $request->input('columns', []);
        $records = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('JO Inventory');

        // ── Headers and Filter Setup ──────────────────────────────────────
        $allCols = [
            'charges' => 'CHARGES',
            'family_name' => 'FAMILY NAME',
            'first_name' => 'FIRST NAME',
            'mi' => 'M.I.',
            'ext' => 'EXT',
            'position' => 'POSITION',
            'nature_of_work' => 'NATURE OF WORK',
            'office' => 'OFFICE',
            'rate_day' => 'RATE/DAY',
            'first_day' => 'FIRST DAY OF SERVICE',
            'length_yrs' => 'LENGTH (YRS)',
            'length_mos' => 'LENGTH (MOS)',
            'birthdate' => 'BIRTHDATE',
            'address' => 'ADDRESS',
            'eligibility' => 'ELIGIBILITY',
            'gender_m' => 'GENDER (M)',
            'gender_f' => 'GENDER (F)',
            'level_1' => '1st LEVEL',
            'level_2' => '2nd LEVEL',
            'ip' => 'IP COMMUNITY MEMBERSHIP',
            'solo_parent' => 'SOLO PARENT',
            'remarks' => 'REMARKS',
        ];

        $cols = ['NO.'];
        $exportKeys = [];
        foreach ($allCols as $key => $label) {
            if (empty($columns) || in_array($key, $columns)) {
                $cols[] = $label;
                $exportKeys[] = $key;
            }
        }
        
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($cols));

        // ── Title ─────────────────────────────────────────────────────────
        $sheet->mergeCells("A1:{$lastColLetter}1");
        $sheet->setCellValue('A1', 'JO INVENTORY — ' . now()->format('Y'));
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEF4FF']],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(26);

        // ── Sub-header row ────────────────────────────────────────────────
        $sheet->mergeCells("A2:{$lastColLetter}2");
        $sheet->setCellValue('A2', 'Generated: ' . now()->format('F d, Y h:i A') . ' | Total Records: ' . $records->count());
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '64748b']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // ── Column headers (row 3) ────────────────────────────────────────
        foreach ($cols as $i => $label) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . '3';
            $sheet->setCellValue($cell, $label);
        }

        $sheet->getStyle("A3:{$lastColLetter}3")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'AAAAAA']]],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(28);

        // ── Data rows ─────────────────────────────────────────────────────
        $rateDayColLetter = '';
        foreach ($exportKeys as $idx => $key) {
            if ($key === 'rate_day') {
                $rateDayColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($idx + 2);
            }
        }

        foreach ($records as $i => $jo) {
            $row = $i + 4;
            $isEven = ($i % 2 === 0);

            $sheet->setCellValue("A{$row}", $i + 1);
            
            $colIndex = 2;
            foreach ($exportKeys as $key) {
                $val = '';
                switch ($key) {
                    case 'charges': $val = $jo->charges; break;
                    case 'family_name': $val = strtoupper($jo->last_name); break;
                    case 'first_name': $val = $jo->first_name; break;
                    case 'mi': $val = $jo->middle_initial; break;
                    case 'ext': $val = $jo->name_extension; break;
                    case 'position': $val = $jo->position_title; break;
                    case 'nature_of_work': $val = $jo->nature_of_work; break;
                    case 'office': $val = $jo->office; break;
                    case 'rate_day': $val = $jo->rate_per_day; break;
                    case 'first_day': $val = $jo->first_day_of_service ? $jo->first_day_of_service->format('Y-m-d') : ''; break;
                    case 'length_yrs': $val = $jo->first_day_of_service ? $jo->years_of_service : ''; break;
                    case 'length_mos': $val = $jo->first_day_of_service ? $jo->months_of_service : ''; break;
                    case 'birthdate': $val = $jo->birthdate ? $jo->birthdate->format('Y-m-d') : ''; break;
                    case 'address': $val = $jo->address; break;
                    case 'eligibility': $val = $jo->eligibility; break;
                    case 'gender_m': $val = $jo->gender === 'M' ? '/' : ''; break;
                    case 'gender_f': $val = $jo->gender === 'F' ? '/' : ''; break;
                    case 'level_1': $val = $jo->first_level_eligibility ? '/' : ''; break;
                    case 'level_2': $val = $jo->second_level_eligibility ? '/' : ''; break;
                    case 'ip': $val = $jo->ip_community_membership; break;
                    case 'solo_parent': $val = $jo->solo_parent ? '/' : ''; break;
                    case 'remarks': $val = $jo->remarks; break;
                }
                $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . $row;
                $sheet->setCellValue($cell, $val);
                $colIndex++;
            }

            $rowBg = $isEven ? 'FFFFFF' : 'F0F7FF';
            $sheet->getStyle("A{$row}:{$lastColLetter}{$row}")->applyFromArray([
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $rowBg]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
                'font'    => ['size' => 9],
            ]);
        }

        // Number format for Rate/Day column
        $lastDataRow = $records->count() + 3;
        if ($records->count() > 0 && $rateDayColLetter) {
            $sheet->getStyle("{$rateDayColLetter}4:{$rateDayColLetter}{$lastDataRow}")
                ->getNumberFormat()
                ->setFormatCode('#,##0.00');
        }

        // Auto-width
        foreach (range('A', $lastColLetter) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Freeze pane below headers
        $sheet->freezePane('A4');

        $writer   = new Xlsx($spreadsheet);
        $filename = 'JO_Inventory_' . now()->format('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    /**
     * Shared validation rules.
     */
    private function validateRecord(Request $request, ?int $exceptId = null): array
    {
        return $request->validate([
            'charges'                 => 'nullable|string|max:100',
            'last_name'               => 'required|string|max:100',
            'first_name'              => 'required|string|max:100',
            'middle_initial'          => 'nullable|string|max:10',
            'name_extension'          => 'nullable|string|max:20',
            'position_title'          => 'required|string|max:200',
            'nature_of_work'          => 'nullable|string|max:200',
            'office'                  => 'nullable|string|max:200',
            'rate_per_day'            => 'nullable|numeric|min:0',
            'first_day_of_service'    => 'nullable|date',
            'birthdate'               => 'nullable|date',
            'address'                 => 'nullable|string|max:500',
            'eligibility'             => 'nullable|string|max:200',
            'gender'                  => 'nullable|in:M,F',
            'first_level_eligibility' => 'boolean',
            'second_level_eligibility'=> 'boolean',
            'ip_community_membership' => 'nullable|string|max:200',
            'solo_parent'             => 'boolean',
            'remarks'                 => 'nullable|string|max:1000',
            'employee_code'           => 'nullable|string|max:20|unique:job_orders,employee_code' . ($exceptId ? ",{$exceptId}" : ''),
        ]);
    }

    /**
     * Import history — past JO imports from ActivityLog.
     */
    public function importHistory()
    {
        $logs = ActivityLog::whereIn('action', ['Imported JO Data', 'Imported Records'])
            ->latest()
            ->paginate(20);
        return view('job-orders.history', compact('logs'));
    }

    /**
     * Undo a specific JO import.
     */
    public function undoImport($id)
    {
        $log = ActivityLog::whereIn('action', ['Imported JO Data', 'Imported Records'])->findOrFail($id);

        $info = json_decode($log->description, true);
        if (!is_array($info)) {
            $info = [];
            if (preg_match('/Imported (\d+)/', $log->description, $m)) {
                $info['created'] = (int) $m[1];
            }
        }
        
        $createdCount = $info['created'] ?? 0;

        if ($createdCount <= 0) {
            return back()->with('error', 'This import did not create any new records. Cannot undo automatically.');
        }

        $startTime = $log->created_at->copy()->subMinutes(60);
        $endTime = $log->created_at->copy()->addMinutes(5);

        // Fetch JO record IDs
        $joIds = JobOrder::whereBetween('created_at', [$startTime, $endTime])
            ->orderBy('created_at', 'desc')
            ->limit($createdCount)
            ->pluck('id');

        if ($joIds->isEmpty()) {
            return back()->with('error', 'Could not find any records created by this import (they may have already been deleted).');
        }

        $deletedJo = JobOrder::whereIn('id', $joIds)->delete();

        // Fetch Plantilla record IDs holding synced JOs
        $prIds = \App\Models\PlantillaRecord::where('employment_status', 'JO')
            ->whereBetween('created_at', [$startTime, $endTime])
            ->orderBy('created_at', 'desc')
            ->limit($createdCount)
            ->pluck('id');
            
        $deletedPr = \App\Models\PlantillaRecord::whereIn('id', $prIds)->delete();

        if ($deletedJo > 0) {
            $info['created'] = $createdCount - $deletedJo;
            $info['deleted_via_undo'] = ($info['deleted_via_undo'] ?? 0) + $deletedJo;

            $log->update([
                'description' => json_encode($info)
            ]);

            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Undid JO Import',
                'description' => "Deleted {$deletedJo} records from import on " . $log->created_at->format('M d, Y h:i A')
            ]);

            return back()->with('success', "Successfully deleted {$deletedJo} Job Order records (and {$deletedPr} synced 'All Data' entries) created by this import.");
        }

        return back()->with('error', 'Could not find any records created by this import (they may have already been deleted).');
    }
}
