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
        session(['last_index_url' => request()->fullUrl()]);


        $query = JobOrder::query()
            ->whereNull('nature_of_separation')
            ->orderBy('office_department')->orderBy('last_name');

        if ($request->filled('search')) {
            $query->search($request->input('search'));
        }
        if ($request->filled('office')) {
            $query->where('detailed_unit', $request->input('office'));
        }
        if ($request->filled('office_department')) {
            $query->where('office_department', $request->input('office_department'));
        }
        if ($request->filled('detail')) {
            $query->where('nature_of_work_detail', $request->input('detail'));
        }
        if ($request->filled('sex')) {
            $query->where('sex', strtoupper($request->input('sex')));
        }

        // Stats
        $total       = (clone $query)->count();
        $maleCount   = (clone $query)->where('sex', 'M')->count();
        $femaleCount = (clone $query)->where('sex', 'F')->count();

        $byOffice = (clone $query)->reorder()->select('office_department', \DB::raw('count(*) as count'))
            ->groupBy('office_department')
            ->pluck('count', 'office_department')
            ->sortKeys();


        $perPageInput = $request->input('per_page', 50);
        $perPage = $perPageInput === 'all' ? max(1, $total) : (int) $perPageInput;
        $records = $query->paginate($perPage)->withQueryString();

        $offices     = JobOrder::distinct()->orderBy('detailed_unit')->pluck('detailed_unit')->filter()->values();
        $chargesList = JobOrder::distinct()->orderBy('office_department')->pluck('office_department')->filter()->values();

        $detailList  = JobOrder::distinct()->orderBy('nature_of_work_detail')->pluck('nature_of_work_detail')->filter()->values();
        $positions   = JobOrder::distinct()->pluck('position_title')->filter()->map(fn($v) => trim($v))->unique()->sortBy(fn($v) => strtolower($v))->values();

        return view('job-orders.index', compact(
            'records', 'total', 'maleCount', 'femaleCount',
            'byOffice', 'offices', 'chargesList', 'detailList', 'positions'
        ));
    }

    /**
     * Show form to create a new JO record.
     */
    public function create()
    {
        $offices     = JobOrder::distinct()->orderBy('detailed_unit')->pluck('detailed_unit')->filter()->values();
        $chargesList = JobOrder::distinct()->orderBy('office_department')->pluck('office_department')->filter()->values();
        $detailList  = JobOrder::distinct()->orderBy('nature_of_work_detail')->pluck('nature_of_work_detail')->filter()->values();
        $positions   = JobOrder::distinct()->pluck('position_title')->filter()->map(fn($v) => trim($v))->unique()->sortBy(fn($v) => strtolower($v))->values();
        return view('job-orders.create', compact('offices', 'chargesList', 'detailList', 'positions'));
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
                $validated['first_name']  ?? null,
                $validated['date_of_birth'] ?? null,
                null,
                $validated['middle_name'] ?? null,
                $validated['last_name']   ?? null
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

        return redirect(session('last_index_url', route('job-orders.index')))
            ->with('success', 'Job Order record created successfully.');
    }

    /**
     * Show form to edit an existing JO record.
     */
    public function edit(JobOrder $jobOrder)
    {
        $jobOrder->load('attachments');
        $offices     = JobOrder::distinct()->orderBy('detailed_unit')->pluck('detailed_unit')->filter()->values();
        $chargesList = JobOrder::distinct()->orderBy('office_department')->pluck('office_department')->filter()->values();
        $detailList  = JobOrder::distinct()->orderBy('nature_of_work_detail')->pluck('nature_of_work_detail')->filter()->values();
        $positions   = JobOrder::distinct()->pluck('position_title')->filter()->map(fn($v) => trim($v))->unique()->sortBy(fn($v) => strtolower($v))->values();
        return view('job-orders.edit', compact('jobOrder', 'offices', 'chargesList', 'detailList', 'positions'));
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
                $validated['first_name']  ?? null,
                $validated['date_of_birth'] ?? null,
                null,
                $validated['middle_name'] ?? null,
                $validated['last_name']   ?? null
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

        return redirect(session('last_index_url', route('job-orders.index')))
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

        return redirect(session('last_index_url', route('job-orders.index')))
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
        return redirect(session('last_index_url', route('job-orders.index')))->with($type, $message);
    }

    /**
     * Download a blank JO Excel template matching the Google Sheets format.
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Inventory');

        // ── Header row (row 1) ─────────────────────────────────────────────
        // Columns:
        // A=OFFICE  B=LASTNAME  C=FIRSTNAME  D=M.I.  E=EXT.  F=POSITION
        // G=NATURE OF WORK  H=DETAILED UNIT  I=RATE/DAY
        // J=FIRST DAY OF SERVICE  K=LENGTH YR/S  L=MONTH/S
        // M=BIRTHDATE  N=STATUS  O=ADDRESS  P=ELIGIBILITY
        // Q=NATURE OF WORK (detail)  R=SEX  S=LEVEL
        // T=IP COMMUNITY MEMBERSHIP  U=SOLO PARENT  V=REMARKS
        $headers = [
            'A1' => 'OFFICE',
            'B1' => 'LASTNAME',
            'C1' => 'FIRSTNAME',
            'D1' => 'M.I.',
            'E1' => 'EXT.',
            'F1' => 'POSITION',
            'G1' => 'NATURE OF WORK',
            'H1' => 'DETAILED UNIT',
            'I1' => 'RATE/DAY',
            'J1' => 'FIRST DAY OF SERVICE',
            'K1' => 'LENGHT OF SERVICE YEAR/S',
            'L1' => 'MONTH/S',
            'M1' => 'DATE OF BIRTH',
            'N1' => 'STATUS',
            'O1' => 'ADDRESS',
            'P1' => 'ELIGIBILITY',
            'Q1' => 'NATURE OF WORK',
            'R1' => 'SEX',
            'S1' => 'LEVEL',
            'T1' => 'IP COMMUNNITY MEMBERSHIP',
            'U1' => 'SOLO PARENT',
            'V1' => 'REMARKS',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Style the header row
        $sheet->getStyle('A1:V1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'AAAAAA']]],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Add one sample row
        $sample = [
            'A2' => 'BENRO',
            'B2' => 'DELA CRUZ',
            'C2' => 'JUAN',
            'D2' => 'S.',
            'E2' => 'Jr.',
            'F2' => 'ADMINISTRATIVE AIDE II',
            'G2' => 'Clerical services',
            'H2' => 'BENRO',
            'I2' => '678.41',
            'J2' => '2024-01-15',
            'K2' => '1',
            'L2' => '4',
            'M2' => '1990-05-20',
            'N2' => 'SINGLE',
            'O2' => 'Malaybalay City, Bukidnon',
            'P2' => 'NO ELIGIBILITY',
            'Q2' => 'CLERICAL SERVICES',
            'R2' => 'M',
            'S2' => 'M1',
        ];
        foreach ($sample as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }
        $sheet->getStyle('A2:V2')->applyFromArray([
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F7FF']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
            'font'    => ['size' => 9, 'italic' => true, 'color' => ['rgb' => '555555']],
        ]);

        // Note row
        $sheet->mergeCells('A3:V3');
        $sheet->setCellValue('A3', '⚠ SEX: enter M or F. LEVEL: M1, F1, M2, F2. STATUS: SINGLE, MARRIED, WIDOW, etc. Dates: YYYY-MM-DD or YYYY/MM/DD. Delete rows 2–3 before uploading real data.');
        $sheet->getStyle('A3')->applyFromArray([
            'font'      => ['italic' => true, 'color' => ['rgb' => '888888'], 'size' => 8],
            'alignment' => ['wrapText' => true],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFDE7']],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(28);

        // Auto-width columns
        foreach (range('A', 'V') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Freeze pane below header
        $sheet->freezePane('A2');

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
        $query = JobOrder::query()->orderBy('office_department')->orderBy('last_name');

        $statusFilter = $request->input('status_filter', 'active');
        if ($statusFilter === 'active')   $query->whereNull('nature_of_separation');
        elseif ($statusFilter === 'inactive') $query->whereNotNull('nature_of_separation');

        if ($request->filled('office'))       $query->where('office_department', $request->input('office'));
        if ($request->filled('office_department'))      $query->where('office_department', $request->input('office_department'));

        if ($request->filled('sex'))       $query->where('sex', strtoupper($request->input('sex')));

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
        $query = JobOrder::query()->orderBy('office_department')->orderBy('last_name');

        $statusFilter = $request->input('status_filter', 'active');
        if ($statusFilter === 'active')       $query->whereNull('nature_of_separation');
        elseif ($statusFilter === 'inactive') $query->whereNotNull('nature_of_separation');

        if ($request->filled('search'))         $query->search($request->input('search'));
        if ($request->filled('office'))         $query->where('detailed_unit', $request->input('office'));
        if ($request->filled('office_department'))        $query->where('office_department', $request->input('office_department'));

        if ($request->filled('sex'))         $query->where('sex', strtoupper($request->input('sex')));

        $columns = $request->input('columns', []);
        $records = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('JO Inventory');

        // ── Headers and Filter Setup ──────────────────────────────────────
        $allCols = [
            'office_department'        => 'OFFICE',
            'last_name'                => 'LAST NAME',
            'first_name'               => 'FIRST NAME',
            'middle_name'              => 'MIDDLE NAME',
            'name_extension'           => 'SUFFIX',
            'position_title'           => 'POSITION',
            'nature_of_work_detail'    => 'NATURE OF WORK DETAIL',
            'detailed_unit'            => 'DETAILED/ REASSIGNED TO',
            'rate_per_day'             => 'RATE/DAY',
            'first_day_of_service'     => 'FIRST DAY OF SERVICE',
            'date_of_birth'                => 'DATE OF BIRTH',
            'civil_status'             => 'CIVIL STATUS',
            'address'                  => 'ADDRESS',
            'eligibility'              => 'ELIGIBILITY',
            'sex'                   => 'SEX',
            'level'                    => 'LEVEL',
            'first_level_eligibility'  => 'FIRST LEVEL ELIGIBILITY',
            'second_level_eligibility' => 'SECOND LEVEL ELIGIBILITY',
            'ip_community_membership'  => 'IP COMMUNITY MEMBERSHIP',
            'solo_parent'              => 'SOLO PARENT',
            'reemployment'             => 'REEMPLOYMENT',
            'remarks'                  => 'REMARKS',
            'employee_code'            => 'EMPLOYEE CODE',
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
            if ($key === 'rate_per_day') {
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
                    case 'office_department': $val = $jo->office_department; break;
                    case 'last_name': $val = strtoupper($jo->last_name); break;
                    case 'first_name': $val = $jo->first_name; break;
                    case 'middle_name': $val = $jo->middle_name; break;
                    case 'name_extension': $val = $jo->name_extension; break;
                    case 'position_title': $val = $jo->position_title; break;
                    case 'nature_of_work_detail': $val = $jo->nature_of_work_detail; break;
                    case 'detailed_unit': $val = $jo->detailed_unit; break;
                    case 'rate_per_day': $val = $jo->rate_per_day; break;
                    case 'first_day_of_service': $val = $jo->first_day_of_service ? $jo->first_day_of_service->format('Y-m-d') : ''; break;
                    case 'date_of_birth': $val = $jo->date_of_birth ? $jo->date_of_birth->format('Y-m-d') : ''; break;
                    case 'civil_status': $val = $jo->civil_status; break;
                    case 'address': $val = $jo->address; break;
                    case 'eligibility': $val = $jo->eligibility; break;
                    case 'sex': $val = $jo->sex; break;
                    case 'level': $val = $jo->level; break;
                    case 'first_level_eligibility': $val = $jo->first_level_eligibility ? 'Y' : 'N'; break;
                    case 'second_level_eligibility': $val = $jo->second_level_eligibility ? 'Y' : 'N'; break;
                    case 'ip_community_membership': $val = $jo->ip_community_membership; break;
                    case 'solo_parent': $val = $jo->solo_parent ? 'Y' : 'N'; break;
                    case 'reemployment': $val = $jo->reemployment ? 'Y' : 'N'; break;
                    case 'remarks': $val = $jo->remarks; break;
                    case 'employee_code': $val = $jo->employee_code; break;
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
        $lastColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($lastColLetter);
        for ($col = 1; $col <= $lastColIndex; $col++) {
            $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
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
        $rules = [
            'office_department'        => 'nullable|string|max:100',
            'last_name'                => 'required|string|max:100',
            'first_name'               => 'required|string|max:100',
            'middle_name'              => 'nullable|string|max:255',
            'name_extension'           => 'nullable|string|max:20',
            'position_title'           => 'required|string|max:200',
            'nature_of_work_detail'    => 'nullable|string|max:200',
            'detailed_unit'            => 'nullable|string|max:100',
            'rate_per_day'             => 'nullable|numeric|min:0',
            'first_day_of_service'     => 'nullable|date',
            'date_of_birth'                => 'nullable|date',
            'civil_status'             => 'nullable|string|max:50',
            'address'                  => 'nullable|string|max:500',
            'eligibility'              => 'nullable|string|max:200',
            'sex'                   => 'nullable|in:M,F',
            'level'                    => 'nullable|string|max:20',
            'first_level_eligibility'  => 'boolean',
            'second_level_eligibility' => 'boolean',
            'ip_community_membership'  => 'nullable|string|max:200',
            'solo_parent'              => 'boolean',
            'reemployment'             => 'boolean',
            'remarks'                  => 'nullable|string|max:1000',
            'nature_of_separation'     => 'nullable|string|max:100',
            'date_separated'           => 'nullable|date',
            'employee_code'            => 'nullable|string|max:20|unique:plantilla_records,employee_code' . ($exceptId ? ",{$exceptId}" : ''),
        ];

        return $request->validate($rules);
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

    // ── Delete All (Super Admin Only) ─────────────────────────────────────────

    /**
     * Permanently delete ALL Job Order records.
     * Restricted to Super Admin only. For data-reset purposes.
     */
    public function deleteAll(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isSuperAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $joCount = JobOrder::count();

        JobOrder::query()->forceDelete();

        ActivityLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'Deleted All JO Data',
            'description' => "Permanently wiped {$joCount} Job Order record(s).",
        ]);

        return redirect(session('last_index_url', route('job-orders.index')))
            ->with('success', "All {$joCount} Job Order record(s) have been permanently deleted.");
    }
}
