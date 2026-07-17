<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\CasualEmployee;
use App\Models\User;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * One-time reconciliation: syncs the Casual Employees inventory
 * (plantilla_records, employment_status=CASUAL) against a CTemp-style
 * import spreadsheet (25-column layout: the 22-column Casual Import
 * Template plus Employee Code / Solo Parent / Address appended).
 *
 * Rules:
 *  - Existing record (matched by employee_code) -> update ONLY columns
 *    that are blank in the DB or differ from the spreadsheet, never
 *    blank out an existing value with an empty spreadsheet cell.
 *  - Spreadsheet row with no employee_code match -> create new record.
 *  - Active DB record whose employee_code is not present anywhere in the
 *    spreadsheet -> archived (soft-deleted), never hard-deleted.
 *
 * Every create/update/soft-delete goes through Eloquent so this app's
 * existing Spatie Activitylog (logOnlyDirty on PlantillaRecord) captures
 * a field-level audit trail automatically, the same as any other edit.
 */
class SyncCasualFromTemplate extends Command
{
    protected $signature = 'casual:sync-from-template
        {file : Path to the CTemp-style xlsx file}
        {--dry-run : Report what would happen without writing anything}
        {--actor= : User ID to attribute the activity log entry to (required for a live run)}';

    protected $description = 'Reconcile the Casual Employees inventory against a CTemp import spreadsheet (update/create/archive).';

    private const COL = [
        'office' => 1, 'item_old' => 2, 'item_new' => 3, 'position' => 4,
        'last' => 5, 'first' => 6, 'middle' => 7, 'ext' => 8, 'vacant' => 9,
        'sg_c' => 10, 'step_c' => 11, 'amt_c' => 12,
        'sg_p' => 13, 'step_p' => 14, 'amt_p' => 15,
        'incr' => 16, 'prev_rate' => 17, 'cur_rate' => 18,
        'sex' => 19, 'dob' => 20, 'fds' => 21, 'elig' => 22,
        'emp_code' => 23, 'solo_parent' => 24, 'address' => 25,
    ];

    public function handle(): int
    {
        $path = $this->argument('file');
        if (!file_exists($path)) {
            $this->error("File not found: {$path}");
            return Command::FAILURE;
        }
        $dryRun = (bool) $this->option('dry-run');
        $this->info($dryRun ? 'DRY RUN — no database changes will be made.' : 'Live run — changes WILL be written.');

        $sheet = IOFactory::load($path)->getActiveSheet();
        $maxRow = $sheet->getHighestRow();

        $rows = [];
        for ($r = 3; $r <= $maxRow; $r++) {
            $get = fn($key) => $this->cell($sheet, $r, self::COL[$key]);
            $office = $get('office');
            if ($office === null && $get('position') === null) {
                continue; // blank trailing row
            }
            $rows[] = [
                'row' => $r,
                'office_department' => $office,
                'item_no_old' => $get('item_old'),
                'item_no_new' => $get('item_new'),
                'position_title' => $get('position'),
                'last_name' => $get('last'),
                'first_name' => $get('first'),
                'middle_name' => $get('middle'),
                'name_extension' => $get('ext'),
                'is_vacant' => strtoupper((string) $get('vacant')) === 'Y',
                'salary_grade' => $this->toInt($get('sg_c')),
                'step' => $this->toInt($get('step_c')),
                'authorized_annual_salary' => $this->toNum($get('amt_c')),
                'sg_proposed' => $this->toInt($get('sg_p')),
                'step_proposed' => $this->toInt($get('step_p')),
                'salary_proposed' => $this->toNum($get('amt_p')),
                'increase_decrease' => $this->toNum($get('incr')),
                'previous_rate' => $this->toNum($get('prev_rate')),
                'base_salary_amount' => $this->toNum($get('cur_rate')),
                'sex' => $get('sex'),
                'date_of_birth' => $get('dob'),
                'first_day_of_service' => $get('fds'),
                'civil_service_eligibility' => $get('elig'),
                'employee_code' => $get('emp_code'),
                'solo_parent' => $get('solo_parent') !== null ? (strtoupper((string) $get('solo_parent')) === 'Y') : null,
                'address' => $get('address'),
            ];
        }
        $this->info('Spreadsheet rows read: ' . count($rows));

        $existing = CasualEmployee::whereNotNull('employee_code')->get()->keyBy('employee_code');
        $this->info('Active DB records (with employee_code): ' . $existing->count());

        $touchedCodes = [];
        $created = [];
        $updated = [];   // [id, name, changed => [field => [old, new]]]
        $skippedRows = [];

        foreach ($rows as $r) {
            if (empty($r['position_title']) && !$r['is_vacant']) {
                $skippedRows[] = $r['row'];
                continue;
            }

            $code = $r['employee_code'];
            $existingRecord = $code ? ($existing->get($code)) : null;

            $attrs = $r;
            unset($attrs['row']);

            if ($existingRecord) {
                $touchedCodes[] = $code;
                $changes = [];
                // The spreadsheet's middle name is, at best, a bare initial
                // (parsed from a combined "First MI. Last" string) — never a
                // full middle name. If the DB already has anything for it,
                // that existing value is strictly more complete, so only
                // fill this field when the DB side is currently empty;
                // never "correct" a full name down to an initial.
                $gapFillOnly = ['middle_name'];
                $boolFields = ['is_vacant', 'solo_parent'];

                foreach ($attrs as $field => $newVal) {
                    if ($newVal === null || $newVal === '') {
                        continue; // never blank out an existing value with an empty cell
                    }
                    $oldVal = $existingRecord->{$field};
                    if (in_array($field, $gapFillOnly, true) && !empty($oldVal)) {
                        continue;
                    }
                    if (in_array($field, $boolFields, true)) {
                        if ((bool) $oldVal === (bool) $newVal) {
                            continue;
                        }
                        $changes[$field] = [(bool) $oldVal, (bool) $newVal];
                        continue;
                    }
                    $oldCmp = $oldVal instanceof \Carbon\Carbon ? $oldVal->format('Y-m-d') : $oldVal;
                    $newCmp = $field === 'date_of_birth' || $field === 'first_day_of_service'
                        ? (string) $newVal
                        : $newVal;
                    if ((string) $oldCmp !== (string) $newCmp) {
                        $changes[$field] = [$oldCmp, $newCmp];
                    }
                }
                if (!empty($changes)) {
                    if (!$dryRun) {
                        $existingRecord->update(array_intersect_key($attrs, $changes));
                    }
                    $updated[] = [
                        'id' => $existingRecord->id,
                        'employee_code' => $code,
                        'name' => trim($existingRecord->last_name . ', ' . $existingRecord->first_name),
                        'changes' => $changes,
                    ];
                }
            } else {
                if (!$dryRun) {
                    $new = CasualEmployee::create($attrs);
                    $created[] = ['id' => $new->id, 'row' => $r['row'], 'name' => trim(($r['last_name'] ?? '') . ', ' . ($r['first_name'] ?? '')), 'office' => $r['office_department'], 'vacant' => $r['is_vacant']];
                } else {
                    $created[] = ['id' => null, 'row' => $r['row'], 'name' => trim(($r['last_name'] ?? '') . ', ' . ($r['first_name'] ?? '')), 'office' => $r['office_department'], 'vacant' => $r['is_vacant']];
                }
            }
        }

        // Any active DB record whose employee_code never appeared in the spreadsheet -> archive
        $toArchive = $existing->reject(fn($rec) => in_array($rec->employee_code, $touchedCodes, true));
        $archived = [];
        foreach ($toArchive as $rec) {
            $archived[] = ['id' => $rec->id, 'employee_code' => $rec->employee_code, 'name' => trim($rec->last_name . ', ' . $rec->first_name), 'position' => $rec->position_title];
            if (!$dryRun) {
                $rec->delete(); // soft delete = archive
            }
        }

        $this->info('');
        $this->info('=== SUMMARY ===');
        $this->info('Updated:  ' . count($updated));
        $this->info('Created:  ' . count($created));
        $this->info('Archived: ' . count($archived));
        $this->info('Skipped rows (no position/name): ' . count($skippedRows));

        $report = [
            'run_at' => now()->toDateTimeString(),
            'dry_run' => $dryRun,
            'source_file' => $path,
            'counts' => [
                'spreadsheet_rows' => count($rows),
                'updated' => count($updated),
                'created' => count($created),
                'archived' => count($archived),
                'skipped' => count($skippedRows),
            ],
            'updated' => $updated,
            'created' => $created,
            'archived' => $archived,
            'skipped_rows' => $skippedRows,
        ];

        $reportDir = storage_path('app/reports');
        if (!is_dir($reportDir)) {
            mkdir($reportDir, 0755, true);
        }
        $reportPath = $reportDir . '/casual_sync_' . now()->format('Ymd_His') . ($dryRun ? '_dryrun' : '') . '.json';
        file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->info('Full audit report saved to: ' . $reportPath);

        if (!$dryRun && (count($updated) + count($created) + count($archived)) > 0) {
            $actorId = (int) $this->option('actor');
            ActivityLog::create([
                'user_id' => $actorId,
                'action' => 'Synced Casual Data from Template',
                'description' => json_encode([
                    'mode' => 'reconcile',
                    'source_file' => basename($path),
                    'updated' => count($updated),
                    'created' => count($created),
                    'archived' => count($archived),
                    'created_ids' => array_column($created, 'id'),
                    'archived_ids' => array_column($archived, 'id'),
                    'report_file' => basename($reportPath),
                ]),
            ]);
        }

        return Command::SUCCESS;
    }

    private function cell($sheet, int $row, int $col)
    {
        $val = $sheet->getCellByColumnAndRow($col, $row)->getValue();
        if ($val === null) {
            return null;
        }
        if (is_string($val)) {
            $val = trim($val);
            return $val === '' ? null : $val;
        }
        return $val;
    }

    private function toInt($v): ?int
    {
        return ($v !== null && is_numeric($v)) ? (int) $v : null;
    }

    private function toNum($v): ?float
    {
        return ($v !== null && is_numeric($v)) ? (float) $v : null;
    }
}
