<?php

namespace App\Http\Controllers;

use App\Models\PlantillaRecord;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImportController extends Controller
{
    // ── System field definitions (DB column => display label) ──────────────
    public static function systemFields(): array
    {
        return [
            // Optional position fields
            'office_department' => ['label' => 'Organizational Unit', 'required' => false],
            'item_no_new' => ['label' => 'Item (Position Code)', 'required' => false],
            'position_title' => ['label' => 'Position Title', 'required' => false],
            'salary_grade' => ['label' => 'Salary Grade', 'required' => false],
            'authorized_annual_salary' => ['label' => 'Authorized Annual Salary', 'required' => false],
            'base_salary_amount' => ['label' => 'Actual Monthly Salary', 'required' => false],
            'step' => ['label' => 'Step', 'required' => false],
            'area_code' => ['label' => 'Area Code', 'required' => false],
            'area_type' => ['label' => 'Area Type', 'required' => false],
            'level' => ['label' => 'Level', 'required' => false],
            // Required employee fields
            'last_name' => ['label' => 'Last Name', 'required' => true],
            'first_name' => ['label' => 'First Name', 'required' => true],
            'middle_name' => ['label' => 'Middle Name', 'required' => false],
            'sex' => ['label' => 'Sex (M/F)', 'required' => false],
            'religion' => ['label' => 'Religion', 'required' => false],
            'date_of_birth' => ['label' => 'Birthday', 'required' => false],
            'tin' => ['label' => 'TIN', 'required' => false],
            'date_original_appointment' => ['label' => 'Date of Original Appointment', 'required' => false],
            'date_last_promotion' => ['label' => 'Date of Last Promotion', 'required' => false],
            'employment_status' => ['label' => 'Employment Status', 'required' => false],
            'civil_service_eligibility' => ['label' => 'Civil Service Eligibility', 'required' => false],
            'remarks_annotation' => ['label' => 'Comment / Annotation', 'required' => false],
            'is_pwd' => ['label' => 'PWD (Y=Yes)', 'required' => false],
            'indigenous_people' => ['label' => 'Indigenous People', 'required' => false],
            'solo_parent' => ['label' => 'Solo Parent (ID Number)', 'required' => false],
            'abolished' => ['label' => 'Abolished (Y=Yes)', 'required' => false],
            'dissolved' => ['label' => 'Dissolved (Y=Yes)', 'required' => false],
            'gsis_bp_number' => ['label' => 'GSIS BP Number', 'required' => false],
            'position_classification' => ['label' => 'Position Classification', 'required' => false],
            'umid' => ['label' => 'UMID', 'required' => false],
            // Job Order & Casual fields
            'office_department' => ['label' => 'Charges', 'required' => false],
            'name_extension' => ['label' => 'Name Extension (Suffix)', 'required' => false],
            'nature_of_work_detail' => ['label' => 'Nature of Work Detail', 'required' => false],
            'rate_per_day' => ['label' => 'Rate Per Day', 'required' => false],
            'first_day_of_service' => ['label' => 'First Day of Service', 'required' => false],
            'civil_status' => ['label' => 'Civil Status', 'required' => false],
            'address' => ['label' => 'Address', 'required' => false],
            'first_level_eligibility' => ['label' => 'First Level Eligibility', 'required' => false],
            'second_level_eligibility' => ['label' => 'Second Level Eligibility', 'required' => false],
            'reemployment' => ['label' => 'Reemployment', 'required' => false],
            'remarks' => ['label' => 'Remarks', 'required' => false],
            'item_no_old' => ['label' => 'Item No Old', 'required' => false],
            'legislative_district' => ['label' => 'Legislative District', 'required' => false],
            'sg_proposed' => ['label' => 'SG Proposed', 'required' => false],
            'step_proposed' => ['label' => 'Step Proposed', 'required' => false],
            'salary_proposed' => ['label' => 'Salary Proposed', 'required' => false],
            'increase_decrease' => ['label' => 'Increase/Decrease', 'required' => false],
            'previous_rate' => ['label' => 'Previous Rate', 'required' => false],
            'current_rate' => ['label' => 'Current Rate', 'required' => false],
            // Separation / appointment history
            'nature_of_separation' => ['label' => 'Nature of Separation', 'required' => false],
            'date_separated'       => ['label' => 'Date Separated', 'required' => false],
            'employee_code'        => ['label' => 'Employee No.', 'required' => false],
        ];
    }

    // ── Legacy hardcoded header → DB column map (for backward compatibility) ──
    private static function legacyHeaderMap(): array
    {
        return [
            'ORGANIZATIONAL UNIT' => 'office_department',
            'ITEM' => 'item_no_new',
            'POSITION TITLE' => 'position_title',
            'SALARY GRADE' => 'salary_grade',
            'AUTHORIZED ANNUAL SALARY' => 'authorized_annual_salary',
            'ACTUAL MONTHLY SALARY' => 'base_salary_amount',
            'ACTUAL ANNUAL SALARY' => 'base_salary_amount', // Fallback for legacy spreadsheets
            'STEP' => 'step',
            'AREA CODE' => 'area_code',
            'AREA TYPE' => 'area_type',
            'LEVEL' => 'level',
            'LAST NAME' => 'last_name',
            'FIRST NAME' => 'first_name',
            'MIDDLE NAME' => 'middle_name',
            'SEX' => 'sex',
            'RELIGION' => 'religion',
            'DATE OF BIRTH' => 'date_of_birth',
            'TIN' => 'tin',
            'DATE OF ORIGINAL APPOINTMENT' => 'date_original_appointment',
            'DATE OF LAST PROMOTION-APPOINTMENT' => 'date_last_promotion',
            'STATUS' => 'employment_status',
            'CIVIL SERVICE ELIGIBILITY' => 'civil_service_eligibility',
            'COMMENT/ ANNOTATION' => 'remarks_annotation',
            'PWD' => 'is_pwd',
            'INDIGENOUS PEOPLE (Y)' => 'indigenous_people',
            'SOLO PARENT (ID_NUMBER)' => 'solo_parent',
            'ABOLISHED' => 'abolished',
            'DISSOLVED' => 'dissolved',
            'GSIS BP NUMBER' => 'gsis_bp_number',
            'POSITION CLASSIFICATION' => 'position_classification',
            'UMID' => 'umid',
            // Job Order & Casual headers
            'CHARGES' => 'office_department',
            'NAME EXTENSION' => 'name_extension',
            'NATURE OF WORK DETAIL' => 'nature_of_work_detail',
            'RATE PER DAY' => 'rate_per_day',
            'FIRST DAY OF SERVICE' => 'first_day_of_service',
            'CIVIL STATUS' => 'civil_status',
            'ADDRESS' => 'address',
            'FIRST LEVEL ELIGIBILITY' => 'first_level_eligibility',
            'SECOND LEVEL ELIGIBILITY' => 'second_level_eligibility',
            'REEMPLOYMENT' => 'reemployment',
            'REMARKS' => 'remarks',
            'ITEM NO OLD' => 'item_no_old',
            'LEGISLATIVE DISTRICT' => 'legislative_district',
            'SG PROPOSED' => 'sg_proposed',
            'STEP PROPOSED' => 'step_proposed',
            'SALARY PROPOSED' => 'salary_proposed',
            'INCREASE DECREASE' => 'increase_decrease',
            'PREVIOUS RATE' => 'previous_rate',
            'CURRENT RATE' => 'current_rate',
            // Full-database-export column headers (so that file re-imports cleanly)
            'OFFICE'        => 'office_department',
            'TERMINATION'   => 'nature_of_separation',
            'EMPLOYEE NO.'  => 'employee_code',
            'INDIGENOUS PEOPLE' => 'indigenous_people',
            'SOLO PARENT'   => 'solo_parent',
            // Aliases to catch typical CSV headers from older systems
            'SUFFIX'    => 'name_extension',
            'EXTENSION' => 'name_extension',
        ];
    }

    /**
     * Show import page.
     */
    public function index()
    {
        $totalRecords = PlantillaRecord::count();
        return view('imports.index', compact('totalRecords'));
    }

    /**
     * Download a blank Excel template with correct headers and sample rows.
     */
    public function template(): StreamedResponse
    {
        $headers = [
            'ORGANIZATIONAL UNIT',
            'ITEM',
            'POSITION TITLE',
            'SALARY GRADE',
            'AUTHORIZED ANNUAL SALARY',
            'ACTUAL MONTHLY SALARY',
            'STEP',
            'AREA CODE',
            'AREA TYPE',
            'LEVEL',
            'LAST NAME',
            'FIRST NAME',
            'MIDDLE NAME',
            'SEX',
            'RELIGION',
            'DATE OF BIRTH',
            'TIN',
            'DATE OF ORIGINAL APPOINTMENT',
            'DATE OF LAST PROMOTION-APPOINTMENT',
            'STATUS',
            'CIVIL SERVICE ELIGIBILITY',
            'COMMENT/ ANNOTATION',
            'PWD',
            'INDIGENOUS PEOPLE (Y)',
            'SOLO PARENT (ID_NUMBER)',
            'ABOLISHED',
            'DISSOLVED',
            'GSIS BP NUMBER',
            'POSITION CLASSIFICATION',
            'UMID',
            'NAME EXTENSION',
            'CHARGES',
            'NATURE OF WORK DETAIL',
            'RATE PER DAY',
            'FIRST DAY OF SERVICE',
            'CIVIL STATUS',
            'ADDRESS',
            'FIRST LEVEL ELIGIBILITY',
            'SECOND LEVEL ELIGIBILITY',
            'REEMPLOYMENT',
            'REMARKS',
            'ITEM NO OLD',
            'LEGISLATIVE DISTRICT',
            'SG PROPOSED',
            'STEP PROPOSED',
            'SALARY PROPOSED',
            'INCREASE DECREASE',
            'PREVIOUS RATE',
            'CURRENT RATE',
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Plantilla Template');

        // Header row styling
        foreach ($headers as $col => $label) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . '1';
            $sheet->setCellValue($cell, $label);
            $sheet->getStyle($cell)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF312E81']],
                'alignment' => ['horizontal' => 'center'],
            ]);
            $sheet->getColumnDimensionByColumn($col + 1)->setWidth(22);
        }

        // Sample filled row
        $sample = [
            'PROVINCE WIDE',
            '001-001',
            'PROVINCIAL ADMINISTRATOR',
            '26',
            '720000',
            '720000',
            '8',
            '100',
            'URBAN',
            '',
            'DE LA CRUZ',
            'JUAN',
            'REYES',
            'M',
            'ROMAN CATHOLIC',
            '1980-01-15',
            '123-456-789-000',
            '2005-06-01',
            '2020-01-01',
            'PERMANENT',
            'CS PROFESSIONAL',
            '',
            'N',
            '',
            '',
            'N',
            'N',
            '1234567890',
            'Career',
            '1234567890',
        ];

        // Sample vacant row
        $vacant = [
            'PROVINCE WIDE',
            '001-002',
            'ADMIN OFFICER II',
            '11',
            '216000',
            '216000',
            '1',
            '100',
            'URBAN',
            '',
            'VACANT',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            'N',
            '',
            '',
            'N',
            'N',
            '',
            'Career',
            '',
        ];

        foreach ($sample as $col => $val) {
            $sheet->setCellValueByColumnAndRow($col + 1, 2, $val);
        }
        foreach ($vacant as $col => $val) {
            $sheet->setCellValueByColumnAndRow($col + 1, 3, $val);
        }

        // Light yellow for sample rows
        $sheet->getStyle('A2:AD2')->getFill()->setFillType('solid')->getStartColor()->setARGB('FFFFFDE7');
        $sheet->getStyle('A3:AD3')->getFill()->setFillType('solid')->getStartColor()->setARGB('FFFFF8E1');

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'plantilla_import_template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Import history — past imports from ActivityLog.
     */
    public function history()
    {
        $logs = ActivityLog::where('action', 'Imported Data')
            ->latest()
            ->paginate(20);
        return view('imports.history', compact('logs'));
    }

    /**
     * Download failed rows from a previous import (stored in session).
     */
    public function exportErrors(): StreamedResponse
    {
        $errorRows = session('import_error_rows', []);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Failed Rows');

        if (empty($errorRows)) {
            $sheet->setCellValue('A1', 'No failed rows found. Please run an import first.');
        } else {
            // Write headers from first row
            $firstRow = reset($errorRows);
            $colHeaders = array_keys($firstRow['data']);
            array_unshift($colHeaders, 'ERROR_REASON');

            foreach ($colHeaders as $col => $label) {
                $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . '1';
                $sheet->setCellValue($cell, strtoupper($label));
                $sheet->getStyle($cell)->getFont()->setBold(true);
                $sheet->getStyle($cell)->getFill()->setFillType('solid')->getStartColor()->setARGB('FFFFE0E0');
            }

            foreach ($errorRows as $rowIdx => $errRow) {
                $excelRow = $rowIdx + 2;
                $sheet->setCellValueByColumnAndRow(1, $excelRow, $errRow['reason']);
                foreach (array_values($errRow['data']) as $col => $val) {
                    $sheet->setCellValueByColumnAndRow($col + 2, $excelRow, $val);
                }
            }
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'import_failed_rows_' . now()->format('Ymd_His') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════════
    // STEP 1 → 2: Read file headers, store temp file, show mapping UI
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Download error rows found during the PREVIEW stage — before the import executes.
     * Stored in session during map() so users can fix mistakes before committing.
     */
    public function exportPreviewErrors(Request $request): StreamedResponse
    {
        $tmpKey   = $request->query('tmp_key', '');
        $errorRows = $tmpKey
            ? session("import_preview_errors_{$tmpKey}", [])
            : [];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Preview Errors');

        if (empty($errorRows)) {
            $sheet->setCellValue('A1', 'No error rows found in this preview session.');
        } else {
            $firstRow   = reset($errorRows);
            $colHeaders = array_keys($firstRow['data']);
            array_unshift($colHeaders, 'ERROR_REASON');

            foreach ($colHeaders as $col => $label) {
                $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . '1';
                $sheet->setCellValue($cell, strtoupper($label));
                $sheet->getStyle($cell)->getFont()->setBold(true);
                $sheet->getStyle($cell)->getFill()->setFillType('solid')->getStartColor()->setARGB('FFFFE0E0');
            }

            foreach ($errorRows as $rowIdx => $errRow) {
                $excelRow = $rowIdx + 2;
                $sheet->setCellValueByColumnAndRow(1, $excelRow, $errRow['reason']);
                foreach (array_values($errRow['data']) as $col => $val) {
                    $sheet->setCellValueByColumnAndRow($col + 2, $excelRow, $val);
                }
            }
        }

        $writer = new Xlsx($spreadsheet);
        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'preview_errors_' . now()->format('Ymd_His') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Return to the column-mapping step using the same uploaded file + previously
     * selected mapping (both stored in session). No re-upload required.
     */
    public function backToMap(Request $request)
    {
        $tmpKey = $request->input('tmp_key');
        $tmpPath = $tmpKey ? session("import_tmp_{$tmpKey}") : null;

        if (!$tmpPath || !Storage::exists($tmpPath)) {
            return redirect()->route('imports.index')
                ->with('error', 'Your session has expired. Please re-upload the file to start over.');
        }

        $headers      = session("import_headers_{$tmpKey}", []);
        $totalRows    = session("import_totalrows_{$tmpKey}", 0);
        $lastMapping  = session("import_lastmapping_{$tmpKey}", []);
        $originalFileName = session("import_filename_{$tmpKey}", '');
        $replaceAll   = (bool) $request->input('replace_all', false);
        $dryRun       = (bool) $request->input('dry_run', false);
        $routingMode  = session("import_routing_mode_{$tmpKey}", 'global');
        $granularStatus = session("import_granular_status_{$tmpKey}", '');
        $systemFields = self::systemFields();

        // Use the previously selected mapping as the pre-selected auto-mapping
        $autoMapping  = $lastMapping;

        return view('imports.map', compact(
            'headers',
            'tmpKey',
            'tmpPath',
            'totalRows',
            'replaceAll',
            'dryRun',
            'systemFields',
            'autoMapping',
            'originalFileName',
            'routingMode',
            'granularStatus'
        ));
    }

    /**
     * Accept uploaded file, extract headers, store temp file, go to mapping.
     */
    public function readHeaders(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ]);

        try {
            $file = $request->file('file');

            // Store the file temporarily so we don't need re-upload later
            $tmpKey = uniqid('import_', true);
            $tmpPath = $file->storeAs('tmp/imports', $tmpKey . '.' . $file->getClientOriginalExtension());

            // FIX #2 — Store the resolved tmp_path in session keyed by tmpKey so the
            // browser-side hidden input can never be tampered to point at another file.
            session(["import_tmp_{$tmpKey}" => $tmpPath]);

            $rows = Excel::toArray(new class implements \Maatwebsite\Excel\Concerns\ToArray {
                public function array(array $array): void
                {
                }
            }, $file);
            $data = $rows[0];

            // Smart header detection: find the actual column-header row even if the file
            // has title/metadata rows at the top (e.g. exported All-Data Excel reports).
            $headerRowIndex = $this->detectHeaderRow($data);
            $headers = array_map('trim', $data[$headerRowIndex] ?? []);
            $headers = array_filter($headers, fn($h) => $h !== '' && $h !== null);
            $headers = array_values($headers);

            // Data rows start AFTER the detected header row
            $totalRows = count($data) - $headerRowIndex - 1;
            $replaceAll = $request->boolean('replace_all');
            $dryRun = $request->boolean('dry_run');
            $routingMode = $request->input('routing_mode', 'global');
            $granularStatus = $request->input('granular_status', '');
            $systemFields = self::systemFields();
            $legacyMap = self::legacyHeaderMap();
            $originalFileName = $file->getClientOriginalName();

            // Auto-detect mapping: if file headers exactly match legacy headers, pre-map them
            $autoMapping = [];
            foreach ($legacyMap as $legacyHeader => $dbCol) {
                foreach ($headers as $fileHeader) {
                    if (strtoupper(trim($fileHeader)) === strtoupper(trim($legacyHeader))) {
                        $autoMapping[$dbCol] = $fileHeader;
                        break;
                    }
                }
            }

            // Store headers + totalRows in session so "Back to Mapping" can restore the map page
            // without requiring a re-upload.
            session([
                "import_headers_{$tmpKey}"   => $headers,
                "import_totalrows_{$tmpKey}" => $totalRows,
                "import_filename_{$tmpKey}"  => $originalFileName,
                "import_routing_mode_{$tmpKey}" => $routingMode,
                "import_granular_status_{$tmpKey}" => $granularStatus,
            ]);

            return view('imports.map', compact(
                'headers',
                'tmpKey',
                'tmpPath',
                'totalRows',
                'replaceAll',
                'dryRun',
                'systemFields',
                'autoMapping',
                'originalFileName',
                'routingMode',
                'granularStatus'
            ));

        } catch (\Exception $e) {
            return redirect()->route('imports.index')
                ->with('error', 'Error reading file: ' . $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // STEP 2 → 3: Accept mapping, run preview
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Accept user's column mapping, load temp file, run preview logic.
     */
    public function map(Request $request)
    {
        $request->validate([
            'tmp_key' => 'required|string|max:100',
            'mapping' => 'required|array',
        ]);

        // FIX #2 — Resolve tmp_path from session instead of trusting the hidden input
        $tmpKey  = $request->input('tmp_key');
        $tmpPath = session("import_tmp_{$tmpKey}");
        $mapping = $request->input('mapping'); // ['office_department' => 'Dept', ...]
        $replaceAll = $request->boolean('replace_all');
        $dryRun = $request->boolean('dry_run');
        $routingMode = $request->input('routing_mode', 'global');
        $granularStatus = $request->input('granular_status', '');
        $originalFileName = $request->input('original_filename', '');

        if (!$tmpPath || !Storage::exists($tmpPath)) {
            return redirect()->route('imports.index')
                ->with('error', 'Temporary file not found or session expired. Please re-upload your file.');
        }

        try {
            $fullPath = Storage::path($tmpPath);
            $rows = Excel::toArray(new class implements \Maatwebsite\Excel\Concerns\ToArray {
                public function array(array $array): void
                {
                }
            }, $fullPath);
            $data = $rows[0];

            // Skip title/metadata rows — find the actual column-header row
            $headerRowIndex = $this->detectHeaderRow($data);
            $header = array_map('trim', $data[$headerRowIndex]);
            $data = array_slice($data, $headerRowIndex + 1); // data rows only
            $totalRows = count($data);

            $allErrors  = [];
            $errorRows  = []; // raw rows for pre-import download
            $duplicates = [];
            $mapped     = [];
            $vacantCount = 0;
            $rowNum = 2;

            $existingItems = PlantillaRecord::pluck('item_no_new')
                ->filter(fn($v) => is_string($v) || is_int($v))
                ->mapWithKeys(fn($item) => [$item => true])
                ->toArray();

            foreach ($data as $row) {
                $result = $this->mapRow($row, $header, $rowNum, $mapping);

                if (!empty($result['errors'])) {
                    $allErrors[] = $result['errors'];
                    $rawData = array_combine(
                        array_map('trim', $header),
                        array_pad(array_values($row), count($header), null)
                    );
                    $errorRows[] = ['reason' => $result['errors'], 'data' => $rawData];
                } else {
                    if (count($mapped) < 20) {
                        $mapped[] = $result['data'];
                    }
                    $itemCode = $result['data']['item_no_new'] ?? '';
                    if ($itemCode && isset($existingItems[$itemCode])) {
                        $duplicates[] = $itemCode;
                    }
                    if ($result['data']['is_vacant'] ?? false) {
                        $vacantCount++;
                    }
                }
                $rowNum++;
            }

            // Store pre-import error rows in session so user can download them now
            session(["import_preview_errors_{$tmpKey}" => $errorRows]);

            // Store the mapping in session for "Back to Mapping" feature
            session(["import_lastmapping_{$tmpKey}" => $mapping]);

            // Build change-comparison for UPDATE rows in the preview (first 10 dupes only, for performance)
            $changeComparisons = [];
            $dupeItemsInPreview = array_filter($mapped, fn($r) => isset($existingItems[$r['item_no_new'] ?? '']));
            $dupeItemsInPreview = array_slice(array_values($dupeItemsInPreview), 0, 10);
            if (!empty($dupeItemsInPreview)) {
                $itemCodesToFetch = array_column($dupeItemsInPreview, 'item_no_new');
                $existingRecords  = PlantillaRecord::whereIn('item_no_new', $itemCodesToFetch)->get()->keyBy('item_no_new');
                $compareFields = ['position_title', 'salary_grade', 'step', 'employment_status',
                                  'first_name', 'last_name', 'office_department'];
                foreach ($dupeItemsInPreview as $row) {
                    $item = $row['item_no_new'] ?? null;
                    if (!$item || !isset($existingRecords[$item])) continue;
                    $existing = $existingRecords[$item];
                    $changes = [];
                    foreach ($compareFields as $field) {
                        $newVal = $row[$field] ?? null;
                        $oldVal = $existing->$field ?? null;
                        if ($newVal !== null && (string)$newVal !== (string)$oldVal) {
                            $changes[] = [
                                'field' => $field,
                                'old'   => $oldVal ?? '—',
                                'new'   => $newVal,
                            ];
                        }
                    }
                    if (!empty($changes)) {
                        $changeComparisons[$item] = $changes;
                    }
                }
            }

            // Last import info for the info banner
            $lastImport = \App\Models\ActivityLog::where('action', 'Imported Data')
                ->with('user')
                ->latest()
                ->first();
            $lastImportInfo = null;
            if ($lastImport) {
                $info = json_decode($lastImport->description, true) ?? [];
                $lastImportInfo = [
                    'date'     => $lastImport->created_at->format('M d, Y h:i A'),
                    'by'       => optional($lastImport->user)->name ?? 'Unknown',
                    'created'  => $info['created'] ?? 0,
                    'updated'  => $info['updated'] ?? 0,
                    'file'     => $info['file_name'] ?? '',
                ];
            }

            // Estimated processing time (rough: ~500 rows/sec for upsert)
            $estSeconds = max(5, (int) round($totalRows / 500));
            $estTime = $estSeconds < 60
                ? "~{$estSeconds} seconds"
                : '~' . round($estSeconds / 60, 1) . ' minutes';

            // Encode the mapping as JSON for passing through to execute
            $mappingJson = json_encode($mapping);
            $tmpKey = $request->input('tmp_key');
            $hasPreviewErrors = count($errorRows) > 0;

            return view('imports.preview', compact(
                'mapped',
                'allErrors',
                'totalRows',
                'replaceAll',
                'dryRun',
                'routingMode',
                'granularStatus',
                'duplicates',
                'mappingJson',
                'tmpKey',
                'tmpPath',
                'originalFileName',
                'vacantCount',
                'changeComparisons',
                'lastImportInfo',
                'estTime',
                'hasPreviewErrors'
            ));

        } catch (\Exception $e) {
            return redirect()->route('imports.index')
                ->with('error', 'Error processing mapping: ' . $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // STEP 3 (legacy entry): Direct upload without mapping
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Handle file upload and show preview (first 20 rows).
     * Legacy path — used when coming from old direct-upload flow.
     */
    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ]);

        try {
            $file = $request->file('file');
            $rows = Excel::toArray(new class implements \Maatwebsite\Excel\Concerns\ToArray {
                public function array(array $array): void
                {
                }
            }, $file);
            $data = $rows[0];

            // Smart header detection — skip title/metadata rows
            $headerRowIndex = $this->detectHeaderRow($data);
            $header = array_map('trim', $data[$headerRowIndex]);
            $data = array_slice($data, $headerRowIndex + 1);
            $totalRows = count($data);

            $allErrors = [];
            $duplicates = [];
            $mapped = [];
            $rowNum = $headerRowIndex + 2; // Excel row number accounts for skipped rows

            $existingItems = PlantillaRecord::pluck('item_no_new')
                ->filter(fn($v) => is_string($v) || is_int($v))
                ->mapWithKeys(fn($item) => [$item => true])
                ->toArray();

            foreach ($data as $row) {
                $result = $this->mapRow($row, $header, $rowNum); // no custom mapping = legacy mode

                if (!empty($result['errors'])) {
                    $allErrors[] = $result['errors'];
                } else {
                    if (count($mapped) < 20) {
                        $mapped[] = $result['data'];
                    }
                    $itemCode = $result['data']['item_no_new'] ?? '';
                    if ($itemCode && isset($existingItems[$itemCode])) {
                        $duplicates[] = $itemCode;
                    }
                }
                $rowNum++;
            }

            $replaceAll = $request->boolean('replace_all');
            $dryRun = $request->boolean('dry_run');
            $routingMode = $request->input('routing_mode', 'global');
            $granularStatus = $request->input('granular_status', '');
            $mappingJson = null;
            $tmpKey = null;
            $tmpPath = null;
            // FIX #4 — Capture filename from the fresh upload in legacy path
            $originalFileName = $file->getClientOriginalName();

            return view('imports.preview', compact(
                'mapped',
                'allErrors',
                'totalRows',
                'replaceAll',
                'dryRun',
                'duplicates',
                'mappingJson',
                'tmpKey',
                'tmpPath'
            ));

        } catch (\Exception $e) {
            return redirect()->route('imports.index')
                ->with('error', 'Error reading file: ' . $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // EXECUTE
    // ══════════════════════════════════════════════════════════════════════════

    /**
     * Execute the full import from the uploaded file.
     * Supports both: (a) temp-file + mappingJson path, (b) fresh file upload path.
     */
    public function execute(Request $request)
    {
        $mappingJson = $request->input('mapping_json');
        $mapping     = $mappingJson ? json_decode($mappingJson, true) : null;

        // Capture original filename for history display
        $originalFileName = $request->input('original_filename', '');

        // FIX #2 — Resolve tmp_path from session via tmp_key, never trust the hidden input
        $tmpKey  = $request->input('tmp_key');
        $tmpPath = $tmpKey ? session("import_tmp_{$tmpKey}") : null;

        // Determine file source: temp file (from mapping flow) or fresh upload
        if ($tmpPath && Storage::exists($tmpPath)) {
            $fullPath = Storage::path($tmpPath);
            if (empty($originalFileName)) {
                $originalFileName = basename($tmpPath);
            }
        } else {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv|max:20480',
            ]);
            $uploadedFile = $request->file('file');
            if (empty($originalFileName)) {
                $originalFileName = $uploadedFile->getClientOriginalName();
            }
            $fullPath = $uploadedFile->getRealPath();
            $tmpPath  = null; // no temp file to delete
        }

        try {
            $replaceAll = $request->boolean('replace_all');
            $dryRun = $request->boolean('dry_run');
            $routingMode = $request->input('routing_mode', 'global');
            $granularStatus = $request->input('granular_status');
            
            $rows = Excel::toArray(new class implements \Maatwebsite\Excel\Concerns\ToArray {
                public function array(array $array): void
                {
                }
            }, $fullPath);
            $data = $rows[0];

            // Skip title/metadata rows — find the actual column-header row
            $headerRowIndex = $this->detectHeaderRow($data);
            $header = array_map('trim', $data[$headerRowIndex]);
            $data = array_slice($data, $headerRowIndex + 1); // data rows only

            $stats = [
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
                'deleted' => 0,
                'errors' => [],
                'created_records' => [],
                'updated_records' => [],
                'dry_run' => $dryRun,
            ];

            $errorRows = [];

            DB::beginTransaction();

            if ($replaceAll && !$dryRun) {
                $stats['deleted'] = PlantillaRecord::count();
                // Disable FK checks so we can wipe the table cleanly,
                // then delete child records (attachments) before the parent.
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                DB::table('employee_attachments')->delete();
                DB::table('plantilla_records')->delete();
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } elseif ($replaceAll && $dryRun) {
                $stats['deleted'] = PlantillaRecord::count(); // show what WOULD be deleted
            }

            foreach ($data as $rowIndex => $row) {
                $rowNum = $rowIndex + 2;
                try {
                    $result = $this->mapRow($row, $header, $rowNum, $mapping);

                    if (!empty($result['errors'])) {
                        $stats['skipped']++;
                        $stats['errors'][] = $result['errors'];
                        $rawData = array_combine(
                            array_map('trim', $header),
                            array_pad(array_values($row), count($header), null)
                        );
                        $errorRows[] = ['reason' => $result['errors'], 'data' => $rawData];
                        continue;
                    }

                    $mappedRow = $result['data'];
                    unset($mappedRow['row_number']);
                    
                    // Granular routing mode validation
                    if ($routingMode === 'granular' && $granularStatus) {
                        $rowStatus = $mappedRow['employment_status'] ?? '';
                        if (!empty($rowStatus) && strcasecmp(trim($rowStatus), trim($granularStatus)) !== 0) {
                            $stats['skipped']++;
                            $stats['errors'][] = "Row {$rowNum}: Status mismatch (expected {$granularStatus}, got {$rowStatus})";
                            continue;
                        }
                        // Default to the granular status if missing
                        if (empty($rowStatus) && !$mappedRow['is_vacant']) {
                            $mappedRow['employment_status'] = $granularStatus;
                        }
                    }

                    // Conditional Processing: Upsert Match Logic
                    $existing = null;
                    if (!$replaceAll) {
                        $itemCode = $mappedRow['item_no_new'] ?? null;
                        $tin = $mappedRow['tin'] ?? null;

                        // Priority 1: Match by Item No + TIN
                        if ($itemCode && $tin) {
                            $existing = PlantillaRecord::withTrashed()
                                ->where('item_no_new', $itemCode)
                                ->where('tin', $tin)
                                ->first();
                        }
                        
                        // Priority 2: Fallback to Item No alone if no TIN provided
                        if (!$existing && $itemCode) {
                            $existing = PlantillaRecord::withTrashed()->where('item_no_new', $itemCode)->first();
                        }

                        // Priority 3: Fallback to Name + Suffix
                        if (!$existing && !empty($mappedRow['first_name']) && !empty($mappedRow['last_name'])) {
                            $query = PlantillaRecord::withTrashed()
                                ->where('first_name', $mappedRow['first_name'])
                                ->where('last_name', $mappedRow['last_name']);
                                
                            if (!empty($mappedRow['middle_name'])) {
                                $query->where('middle_name', $mappedRow['middle_name']);
                            }
                            if (!empty($mappedRow['name_extension'])) {
                                $query->where('name_extension', $mappedRow['name_extension']);
                            }
                            
                            $existing = $query->first();
                        }
                    }

                    if ($existing) {
                        // Restore if it was soft-deleted
                        if ($existing->trashed()) {
                            $existing->restore();
                        }
                        
                        // Only update with fields that are not null, protecting existing data from being wiped by partial imports
                        $updateData = array_filter($mappedRow, fn($value) => $value !== null);
                        $existing->update($updateData);
                        $stats['updated']++;
                        // FIX #6 — Cap detail arrays at 100 to avoid memory/rendering issues on large imports
                        if (count($stats['updated_records']) < 100) {
                            $itemName = $existing->item_no_new ?: 'No Item';
                            $stats['updated_records'][] = trim($existing->first_name . ' ' . $existing->last_name) . " ({$itemName})";
                        }
                    } else {
                        $newRecord = PlantillaRecord::create($mappedRow);
                        $stats['created']++;
                        // FIX #6 — Cap detail arrays at 100
                        if (count($stats['created_records']) < 100) {
                            $itemName = $newRecord->item_no_new ?: 'No Item';
                            $stats['created_records'][] = trim($newRecord->first_name . ' ' . $newRecord->last_name) . " ({$itemName})";
                        }
                    }

                } catch (\Exception $e) {
                    $stats['errors'][] = "Row {$rowNum}: " . $e->getMessage();
                    $stats['skipped']++;
                }
            }

            if ($dryRun) {
                DB::rollBack();
            } else {
                DB::commit();
                if (Auth::check()) {
                    ActivityLog::create([
                        'user_id' => Auth::id(),
                        'action' => 'Imported Data',
                        'description' => json_encode([
                            'mode' => $replaceAll ? 'replace_all' : 'upsert',
                            'with_mapping' => $mapping ? true : false,
                            'file_name' => $originalFileName,
                            'created' => $stats['created'],
                            'updated' => $stats['updated'],
                            'deleted' => $stats['deleted'],
                            'skipped' => $stats['skipped'],
                        ]),
                    ]);
                }

                // Clean up temp file AND session key after successful import
                if ($tmpPath && Storage::exists($tmpPath)) {
                    Storage::delete($tmpPath);
                }
                if ($tmpKey) {
                    session()->forget("import_tmp_{$tmpKey}");
                }
            }

            session(['import_error_rows' => $errorRows]);

            return view('imports.results', compact('stats'));

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('imports.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function undoImport($id)
    {
        $log = ActivityLog::where('action', 'Imported Data')->findOrFail($id);

        $info = json_decode($log->description, true) ?? [];
        $createdCount = $info['created'] ?? 0;

        if ($createdCount <= 0) {
            return back()->with('error', 'This import did not create any new records to delete.');
        }

        // The ActivityLog is generated at the end of the import.
        // We look for records created up to 1 hour before the log, just to be safe.
        $startTime = $log->created_at->copy()->subMinutes(60);
        $endTime = $log->created_at->copy()->addMinutes(5);

        // Fetch IDs first because delete() with limit() can sometimes fail depending on DB driver
        $recordIds = PlantillaRecord::whereBetween('created_at', [$startTime, $endTime])
            ->orderBy('created_at', 'desc')
            ->limit($createdCount)
            ->pluck('id');

        if ($recordIds->isEmpty()) {
            return back()->with('error', 'Could not find any records created by this import (they may have already been deleted).');
        }

        $deleted = PlantillaRecord::whereIn('id', $recordIds)->delete();

        if ($deleted > 0) {
            // Update the log so it doesn't show as having created them anymore
            $info['created'] = $createdCount - $deleted;
            $info['deleted_via_undo'] = ($info['deleted_via_undo'] ?? 0) + $deleted;

            // Keep action as 'Imported Data' so it stays in the history table
            $log->update([
                'description' => json_encode($info)
            ]);

            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Undid Import',
                'description' => "Deleted {$deleted} records from import on " . $log->created_at->format('M d, Y h:i A')
            ]);

            return back()->with('success', "Successfully deleted {$deleted} records that were created by this import.");
        }

        return back()->with('error', 'Could not find any records created by this import (they may have already been deleted).');
    }

    // ── Private Helpers ───────────────────────────────────────────────────────

    /**
     * Scan the first up-to-10 rows of a sheet and return the index of the row
     * that is most likely the actual column-header row.
     *
     * Strategy: count how many cells in each row match a known legacy header
     * (case-insensitive). The row with the highest match count wins.
     * Falls back to row 0 if nothing matches (plain template files).
     */
    private function detectHeaderRow(array $data): int
    {
        $legacyHeaders = array_keys(self::legacyHeaderMap()); // known column names
        $bestIndex = 0;
        $bestScore = 0;

        $scanLimit = min(10, count($data));
        for ($i = 0; $i < $scanLimit; $i++) {
            $row = $data[$i] ?? [];
            $score = 0;
            foreach ($row as $cell) {
                $cell = strtoupper(trim((string) $cell));
                if ($cell === '')
                    continue;
                foreach ($legacyHeaders as $h) {
                    if (strtoupper(trim($h)) === $cell) {
                        $score++;
                        break;
                    }
                }
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestIndex = $i;
            }
        }

        return $bestIndex;
    }

    /**
     * Map a raw Excel row to a plantilla_records array.
     *
     * @param array      $row     Raw row values
     * @param array      $header  Raw file headers
     * @param int        $rowNum  Excel row number (for error messages)
     * @param array|null $mapping Custom mapping: ['db_col' => 'Excel Header', ...]
     *                            If null, uses legacy hardcoded header names.
     */
    private function mapRow(array $row, array $header, int $rowNum, ?array $mapping = null): array
    {
        // Pad row to match header length
        while (count($row) < count($header)) {
            $row[] = null;
        }

        // Build key-value lookup: Excel header → cell value
        $data = array_combine($header, $row);

        // Sanitize Excel formula error strings → treat as null
        $sanitize = function (mixed $value): mixed {
            if ($value === null)
                return null;
            $str = trim((string) $value);
            // Excel error values that PhpSpreadsheet reads as literal strings
            $excelErrors = ['#NULL!', '#DIV/0!', '#VALUE!', '#REF!', '#NAME?', '#NUM!', '#N/A', '#ERROR!', '#GETTING_DATA'];
            if (in_array(strtoupper($str), $excelErrors, true)) {
                return null;
            }
            return $value;
        };

        // Build a helper closure: given a db column name, retrieve the cell value
        $get = function (string $dbCol) use ($data, $mapping, $sanitize): mixed {
            if ($mapping) {
                // Custom-mapping mode: look up which Excel column the user picked
                $excelCol = $mapping[$dbCol] ?? null;
                if (!$excelCol)
                    return null;
                return $sanitize($data[$excelCol] ?? null);
            }

            // Legacy mode: use hardcoded header names
            $legacyMap = self::legacyHeaderMap();
            // Find legacy header(s) that point to this dbCol
            foreach ($legacyMap as $legacyHeader => $col) {
                if ($col === $dbCol) {
                    // Try exact match first
                    if (array_key_exists($legacyHeader, $data)) {
                        return $sanitize($data[$legacyHeader]);
                    }
                    // Case-insensitive fallback
                    foreach ($data as $k => $v) {
                        if (strtoupper(trim($k)) === strtoupper(trim($legacyHeader))) {
                            return $sanitize($v);
                        }
                    }
                }
            }
            return null;
        };

        $errors = [];

        // Pre-compute last name and vacant state before validation
        $lastNameRaw = trim((string) ($get('last_name') ?? ''));
        $firstNameRaw = trim((string) ($get('first_name') ?? ''));
        $isVacantKeyword = strtoupper($lastNameRaw) === 'VACANT';
        $isVacant = $isVacantKeyword || (empty($lastNameRaw) && empty($firstNameRaw));

        // Required field validation: first name and last name (skip for explicitly vacant rows)
        if (!$isVacant) {
            if (empty($lastNameRaw)) {
                $errors[] = "Row {$rowNum}: Missing Last Name";
            }
            if (empty($firstNameRaw)) {
                $errors[] = "Row {$rowNum}: Missing First Name";
            }
        }

        if ($errors) {
            return ['data' => null, 'errors' => implode(' | ', $errors)];
        }

        $sexRaw = strtoupper(trim((string) ($get('sex') ?? '')));

        return [
            'data' => [
                'row_number' => $rowNum,
                'office_department' => trim((string) ($get('office_department') ?? '')) ?: null,
                'item_no_new' => trim((string) ($get('item_no_new') ?? '')) ?: null,
                'position_title' => trim((string) ($get('position_title') ?? '')) ?: null,
                'salary_grade' => ($val = preg_replace('/[^\d]/', '', (string) $get('salary_grade'))) !== '' ? (int) $val : null,
                'authorized_annual_salary' => ($val = preg_replace('/[^\d.]/', '', (string) $get('authorized_annual_salary'))) !== '' ? (float) $val : null,
                'base_salary_amount' => ($val = preg_replace('/[^\d.]/', '', (string) $get('base_salary_amount'))) !== '' ? (float) $val : null,
                'step' => ($val = preg_replace('/[^\d]/', '', (string) $get('step'))) !== '' ? max(1, (int) $val) : null,
                'area_code' => trim((string) ($get('area_code') ?? '')) ?: null,
                'area_type' => trim((string) ($get('area_type') ?? '')) ?: null,
                'level' => trim((string) ($get('level') ?? '')) ?: null,
                'last_name' => $isVacant ? null : $lastNameRaw,
                'first_name' => $isVacant ? null : ($firstNameRaw ?: null),
                'middle_name' => $isVacant ? null : (trim((string) ($get('middle_name') ?? '')) ?: null),
                'sex' => $isVacant ? null : (in_array($sexRaw, ['M', 'F']) ? $sexRaw : null),
                'religion' => $isVacant ? null : (trim((string) ($get('religion') ?? '')) ?: null),
                'date_of_birth' => $this->parseDate($get('date_of_birth')),
                'tin' => trim((string) ($get('tin') ?? '')) ?: null,
                'date_original_appointment' => $this->parseDate($get('date_original_appointment')),
                'date_last_promotion' => $this->parseDate($get('date_last_promotion')),
                'employment_status' => trim((string) ($get('employment_status') ?? '')) ?: null,
                'civil_service_eligibility' => trim((string) ($get('civil_service_eligibility') ?? '')) ?: null,
                'remarks_annotation' => trim((string) ($get('remarks_annotation') ?? '')) ?: null,
                'is_pwd' => strtoupper(trim((string) ($get('is_pwd') ?? ''))) === 'Y',
                'indigenous_people' => strtoupper(trim((string) ($get('indigenous_people') ?? ''))) === 'Y' ? 'Y' : null,
                'solo_parent' => trim((string) ($get('solo_parent') ?? '')) ?: null,
                'abolished' => strtoupper(trim((string) ($get('abolished') ?? ''))) === 'Y',
                'dissolved' => strtoupper(trim((string) ($get('dissolved') ?? ''))) === 'Y',
                'gsis_bp_number' => trim((string) ($get('gsis_bp_number') ?? '')) ?: null,
                'position_classification' => trim((string) ($get('position_classification') ?? '')) ?: null,
                'umid' => trim((string) ($get('umid') ?? '')) ?: null,
                'office_department' => trim((string) ($get('office_department') ?? '')) ?: null,
                'name_extension' => trim((string) ($get('name_extension') ?? '')) ?: null,
                'nature_of_work_detail' => trim((string) ($get('nature_of_work_detail') ?? '')) ?: null,
                'rate_per_day' => ($val = preg_replace('/[^\d.]/', '', (string) $get('rate_per_day'))) !== '' ? (float) $val : null,
                'first_day_of_service' => $this->parseDate($get('first_day_of_service')),
                'civil_status' => trim((string) ($get('civil_status') ?? '')) ?: null,
                'address' => trim((string) ($get('address') ?? '')) ?: null,
                'first_level_eligibility' => strtoupper(trim((string) ($get('first_level_eligibility') ?? ''))) === 'Y',
                'second_level_eligibility' => strtoupper(trim((string) ($get('second_level_eligibility') ?? ''))) === 'Y',
                'reemployment' => strtoupper(trim((string) ($get('reemployment') ?? ''))) === 'Y',
                'remarks' => trim((string) ($get('remarks') ?? '')) ?: null,
                'item_no_old' => trim((string) ($get('item_no_old') ?? '')) ?: null,
                'legislative_district' => trim((string) ($get('legislative_district') ?? '')) ?: null,
                'sg_proposed' => ($val = preg_replace('/[^\d]/', '', (string) $get('sg_proposed'))) !== '' ? (int) $val : null,
                'step_proposed' => ($val = preg_replace('/[^\d]/', '', (string) $get('step_proposed'))) !== '' ? (int) $val : null,
                'salary_proposed' => ($val = preg_replace('/[^\d.]/', '', (string) $get('salary_proposed'))) !== '' ? (float) $val : null,
                'increase_decrease' => ($val = preg_replace('/[^\d.]/', '', (string) $get('increase_decrease'))) !== '' ? (float) $val : null,
                'previous_rate' => ($val = preg_replace('/[^\d.]/', '', (string) $get('previous_rate'))) !== '' ? (float) $val : null,
                'current_rate' => ($val = preg_replace('/[^\d.]/', '', (string) $get('current_rate'))) !== '' ? (float) $val : null,
                'is_vacant' => $isVacant,
            ],
            'errors' => [],
        ];
    }

    /**
     * Parse Excel date: handles numeric serial numbers and string formats.
     */
    private function parseDate(mixed $dateValue): ?string
    {
        if (empty($dateValue))
            return null;

        try {
            if (is_numeric($dateValue)) {
                return \Carbon\Carbon::createFromTimestamp(($dateValue - 25569) * 86400)
                    ->setTimezone('UTC')
                    ->format('Y-m-d');
            }
            foreach (['Y-m-d', 'd/m/Y', 'm/d/Y', 'Y/m/d'] as $fmt) {
                try {
                    return \Carbon\Carbon::createFromFormat($fmt, trim($dateValue))->format('Y-m-d');
                } catch (\Exception) {
                }
            }
        } catch (\Exception) {
        }

        return null;
    }
}
