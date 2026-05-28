<?php

namespace App\Imports;

use App\Models\CasualEmployee;
use App\Models\PlantillaRecord;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Carbon\Carbon;

/**
 * Casual Employee Inventory Import.
 *
 * Supports TWO file formats:
 *
 * FORMAT A – Simple Template (created by downloadTemplate()):
 *   Row 1: Title row (skipped)
 *   Row 2: Column headers
 *   Row 3+: Data rows
 *   Columns: OFFICE | ITEM OLD | ITEM NEW | POSITION | LAST NAME | FIRST NAME |
 *            MI | EXT | VACANT | SG CUR | STEP CUR | SALARY CUR |
 *            SG PROP | STEP PROP | SALARY PROP | INCREASE | PREV RATE |
 *            CUR RATE | GENDER | BIRTHDATE | FIRST DAY | ELIGIBILITY | REMARKS
 *
 * FORMAT B – Native Plantilla of Personnel Excel:
 *   Detects the "CASUAL" section header automatically.
 *   Reads rows after it until the "TOTAL" row or end of sheet.
 *   Columns are matched by position from the Plantilla layout.
 */
class CasualImport implements ToCollection, WithStartRow
{
    public array $errors     = [];
    public int   $imported   = 0;
    public int   $skipped    = 0;

    /** IDs created by this import — used for reliable undo */
    public array $createdCasualIds    = [];
    public array $createdPlantillaIds = [];

    /** Which format was detected */
    private string $format = 'simple'; // 'simple' or 'plantilla'

    /** Row where actual data starts (used only for simple format) */
    public function startRow(): int
    {
        return 3; // Row 1 = title, Row 2 = headers, Row 3+ = data
    }

    public function collection(Collection $rows): void
    {
        // ── Detect if this is a native Plantilla file ──────────────────────
        // Plantilla files have "PLANTILLA" somewhere in the first few rows
        $this->format = $this->detectFormat($rows);

        if ($this->format === 'plantilla') {
            $this->importPlantillaFormat($rows);
        } else {
            $this->importSimpleFormat($rows);
        }
    }

    // ── FORMAT DETECTION ──────────────────────────────────────────────────

    private function detectFormat(Collection $rows): string
    {
        foreach ($rows->take(10) as $row) {
            $joined = strtoupper(implode(' ', $row->toArray()));
            if (str_contains($joined, 'PLANTILLA') || str_contains($joined, 'PROVINCE') || str_contains($joined, 'BUKIDNON')) {
                return 'plantilla';
            }
        }
        return 'simple';
    }

    // ── SIMPLE TEMPLATE FORMAT ────────────────────────────────────────────

    /**
     * Simple template columns (0-indexed):
     * 0=OFFICE, 1=ITEM_OLD, 2=ITEM_NEW, 3=POSITION, 4=LAST_NAME, 5=FIRST_NAME,
     * 6=MI, 7=EXT, 8=VACANT(Y), 9=SG_CUR, 10=STEP_CUR, 11=SALARY_CUR,
     * 12=SG_PROP, 13=STEP_PROP, 14=SALARY_PROP, 15=INCREASE,
     * 16=PREV_RATE, 17=CUR_RATE, 18=GENDER, 19=BIRTHDATE,
     * 20=FIRST_DAY, 21=ELIGIBILITY, 22=REMARKS
     */
    private function importSimpleFormat(Collection $rows): void
    {
        foreach ($rows as $rowIndex => $row) {
            $actualRow = $rowIndex + 3;

            $rowArr = $row->toArray();
            if (empty(array_filter($rowArr, fn($v) => $v !== null && $v !== ''))) {
                $this->skipped++;
                continue;
            }

            $lastName  = $this->str($row, 4);
            $isVacant  = strtoupper($this->str($row, 8) ?? '') === 'Y' || strtoupper($lastName ?? '') === 'VACANT';

            if (!$isVacant && empty($lastName)) {
                $this->skipped++;
                continue;
            }

            $office = $this->str($row, 0);

            try {
                $casual = CasualEmployee::create([
                    'office'               => $office,
                    'item_no_old'          => $this->str($row, 1),
                    'item_no_new'          => $this->str($row, 2),
                    'position_title'       => $this->str($row, 3),
                    'last_name'            => $isVacant ? null : $lastName,
                    'first_name'           => $isVacant ? null : $this->str($row, 5),
                    'middle_initial'       => $isVacant ? null : $this->str($row, 6),
                    'name_extension'       => $isVacant ? null : $this->str($row, 7),
                    'is_vacant'            => $isVacant,
                    'sg_current'           => $this->int($row, 9),
                    'step_current'         => $this->int($row, 10),
                    'salary_current'       => $this->numeric($row, 11),
                    'sg_proposed'          => $this->int($row, 12),
                    'step_proposed'        => $this->int($row, 13),
                    'salary_proposed'      => $this->numeric($row, 14),
                    'increase_decrease'    => $this->numeric($row, 15),
                    'previous_rate'        => $this->numeric($row, 16),
                    'current_rate'         => $this->numeric($row, 17),
                    'gender'               => $this->gender($row, 18),
                    'birthdate'            => $this->date($row, 19),
                    'first_day_of_service' => $this->date($row, 20),
                    'eligibility'          => $this->str($row, 21),
                ]);

                $plantillaId = $this->syncToAllData($casual, $office);

                $this->createdCasualIds[]    = $casual->id;
                if ($plantillaId) {
                    $this->createdPlantillaIds[] = $plantillaId;
                }
                $this->imported++;

            } catch (\Throwable $e) {
                $this->errors[] = "Row {$actualRow}: " . $e->getMessage();
            }
        }
    }

    // ── NATIVE PLANTILLA FORMAT ───────────────────────────────────────────

    /**
     * In the native Plantilla Excel format:
     *   Col A (0) = Item No Old
     *   Col B (1) = Item No New
     *   Col C (2) = Position Title
     *   Col D (3) = Name of Incumbent (full name in one cell, or "Vacant")
     *   Col E (4) = SG Current
     *   Col F (5) = Step Current (combined "SG/Step" e.g. "1/1")
     *   Col G (6) = Salary Current (LBC #165 Amount)
     *   Col H (7) = SG Proposed
     *   Col I (8) = Step Proposed
     *   Col J (9) = Salary Proposed
     *   Col K (10)= Increase/Decrease
     *   Col L (11)= Previous Rate
     *   Col M (12)= Current Rate
     */
    private function importPlantillaFormat(Collection $rows): void
    {
        $inCasualSection = false;
        $currentOffice   = null;

        foreach ($rows as $rowIndex => $row) {
            $rowArr = $row->toArray();
            $joined = strtoupper(implode(' ', array_map('strval', $rowArr)));

            // Track office name from "Department/Office:" rows
            foreach ($rowArr as $cell) {
                $cellStr = strtoupper(trim((string)$cell));
                if (str_starts_with($cellStr, 'DEPARTMENT/OFFICE:') || str_starts_with($cellStr, 'DEPARTMENT/OFFICE')) {
                    $currentOffice = trim(str_ireplace(['DEPARTMENT/OFFICE:', 'DEPARTMENT/OFFICE'], '', (string)$cell));
                }
            }

            // Detect "CASUAL" section header
            if (!$inCasualSection) {
                if (str_contains($joined, 'CASUAL') && !str_contains($joined, 'CO-TERMINOUS') && !str_contains($joined, 'PERMANENT')) {
                    $inCasualSection = true;
                }
                continue;
            }

            // Stop at TOTAL row
            if (str_contains($joined, 'TOTAL') && !str_contains($joined, 'POSITION')) {
                break;
            }

            // Skip empty rows or sub-header rows
            if (empty(array_filter($rowArr, fn($v) => $v !== null && $v !== ''))) {
                continue;
            }

            // Row col 0 must be a number (item no) or skip
            $itemOld = $this->str($row, 0);
            $itemNew = $this->str($row, 1);
            if ($itemOld && !is_numeric($itemOld) && $itemOld !== '') {
                continue; // header-like row
            }

            $positionTitle = $this->str($row, 2);
            $incumbentRaw  = $this->str($row, 3);
            $isVacant      = empty($incumbentRaw) || strtoupper(trim($incumbentRaw)) === 'VACANT';

            if (empty($positionTitle)) {
                $this->skipped++;
                continue;
            }

            // Parse "SG/Step" combined cell e.g. "1/1" → sg=1, step=1
            [$sgCur, $stepCur]   = $this->parseSgStep($this->str($row, 4) ?? $this->str($row, 5));
            $salaryCur           = $this->numeric($row, 6);
            [$sgProp, $stepProp] = $this->parseSgStep($this->str($row, 7) ?? $this->str($row, 8));
            $salaryProp          = $this->numeric($row, 9);
            $increaseDec         = $this->numeric($row, 10);
            $prevRate            = $this->numeric($row, 11);
            $curRate             = $this->numeric($row, 12);

            // Parse name: "Last Name, First Name MI. Ext" or "DELA CRUZ, JUAN S."
            [$lastName, $firstName, $mi, $ext] = $this->parseName($incumbentRaw, $isVacant);

            try {
                $casual = CasualEmployee::create([
                    'office'               => $currentOffice,
                    'item_no_old'          => $itemOld,
                    'item_no_new'          => $itemNew,
                    'position_title'       => $positionTitle,
                    'is_vacant'            => $isVacant,
                    'last_name'            => $lastName,
                    'first_name'           => $firstName,
                    'middle_initial'       => $mi,
                    'name_extension'       => $ext,
                    'sg_current'           => $sgCur,
                    'step_current'         => $stepCur,
                    'salary_current'       => $salaryCur,
                    'sg_proposed'          => $sgProp,
                    'step_proposed'        => $stepProp,
                    'salary_proposed'      => $salaryProp,
                    'increase_decrease'    => $increaseDec,
                    'previous_rate'        => $prevRate,
                    'current_rate'         => $curRate,
                ]);

                $plantillaId = $this->syncToAllData($casual, $currentOffice);

                $this->createdCasualIds[]    = $casual->id;
                if ($plantillaId) {
                    $this->createdPlantillaIds[] = $plantillaId;
                }
                $this->imported++;

            } catch (\Throwable $e) {
                $this->errors[] = "Row " . ($rowIndex + 1) . ": " . $e->getMessage();
            }
        }
    }

    // ── SYNC TO ALL DATA ─────────────────────────────────────────────────

    private function syncToAllData(CasualEmployee $casual, ?string $office): ?int
    {
        try {
            $pr = PlantillaRecord::create([
                'organizational_unit'       => $office,
                'last_name'                 => $casual->last_name,
                'first_name'                => $casual->first_name,
                'middle_name'               => $casual->middle_initial,
                'position_title'            => $casual->position_title,
                'salary_grade'              => $casual->sg_current,
                'step'                      => $casual->step_current,
                'authorized_annual_salary'  => $casual->salary_current,
                'actual_annual_salary'      => $casual->salary_current,
                'sex'                       => $casual->gender,
                'date_of_birth'             => $casual->birthdate ? $casual->birthdate->format('Y-m-d') : null,
                'date_original_appointment' => $casual->first_day_of_service ? $casual->first_day_of_service->format('Y-m-d') : null,
                'civil_service_eligibility' => $casual->eligibility,
                'employment_status'         => 'Casual',
                'is_vacant'                 => $casual->is_vacant,
                'item'                      => $casual->item_no_new ?? $casual->item_no_old,
            ]);
            return $pr->id;
        } catch (\Throwable) {
            // Sync failure is non-fatal — the casual record still saves.
            return null;
        }
    }

    // ── HELPERS ───────────────────────────────────────────────────────────

    private function str(Collection $row, int $col): ?string
    {
        $val = $row->get($col);
        return ($val !== null && $val !== '') ? trim((string) $val) : null;
    }

    private function numeric(Collection $row, int $col): ?float
    {
        $val = $row->get($col);
        if ($val === null || $val === '') return null;
        $str = str_replace([',', ' '], '', trim((string) $val));
        return is_numeric($str) ? (float) $str : null;
    }

    private function int(Collection $row, int $col): ?int
    {
        $val = $this->numeric($row, $col);
        return $val !== null ? (int) $val : null;
    }

    private function gender(Collection $row, int $col): ?string
    {
        $val = strtoupper(trim((string)($row->get($col) ?? '')));
        return in_array($val, ['M', 'F']) ? $val : null;
    }

    private function date(Collection $row, int $col): ?string
    {
        $val = $row->get($col);
        if ($val === null || $val === '') return null;
        if (is_numeric($val)) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($val))->format('Y-m-d');
            } catch (\Throwable) { return null; }
        }
        try {
            return Carbon::parse($val)->format('Y-m-d');
        } catch (\Throwable) { return null; }
    }

    /**
     * Parse "SG/Step" combined string like "1/1" or "4/1"
     * Returns [sg, step]
     */
    private function parseSgStep(?string $value): array
    {
        if (!$value) return [null, null];
        if (str_contains($value, '/')) {
            $parts = explode('/', $value, 2);
            $sg   = is_numeric(trim($parts[0])) ? (int) trim($parts[0]) : null;
            $step = is_numeric(trim($parts[1])) ? (int) trim($parts[1]) : null;
            return [$sg, $step];
        }
        return [is_numeric($value) ? (int)$value : null, null];
    }

    /**
     * Parse incumbent name from Plantilla format.
     * E.g. "Karola Jun T. Bongcales" → [last='Bongcales', first='Karola Jun', mi='T', ext=null]
     * Or "DELA CRUZ, JUAN S." → [last='DELA CRUZ', first='JUAN', mi='S', ext=null]
     */
    private function parseName(?string $raw, bool $isVacant): array
    {
        if ($isVacant || empty($raw) || strtoupper(trim($raw)) === 'VACANT') {
            return [null, null, null, null];
        }
        $raw = trim($raw);
        // Format: "Last, First MI." → comma-separated
        if (str_contains($raw, ',')) {
            $parts = explode(',', $raw, 2);
            $last  = trim($parts[0]);
            $rest  = trim($parts[1] ?? '');
            $words = explode(' ', $rest);
            $mi    = null;
            $ext   = null;
            // Look for MI (single letter + dot) or extension (Jr., Sr., III)
            $exts  = ['JR.', 'SR.', 'II', 'III', 'IV'];
            $firstParts = [];
            foreach ($words as $w) {
                $wu = strtoupper(trim($w, '.'));
                if (in_array(strtoupper($w), $exts) || in_array($wu . '.', $exts)) {
                    $ext = $w;
                } elseif (strlen($w) <= 2 && str_ends_with($w, '.')) {
                    $mi = rtrim($w, '.');
                } else {
                    $firstParts[] = $w;
                }
            }
            return [$last, implode(' ', $firstParts), $mi, $ext];
        }
        // Format: "First [MI.] Last [Ext]" — treat last word as last name
        $words = explode(' ', $raw);
        if (count($words) === 1) {
            return [$raw, null, null, null];
        }
        $last  = array_pop($words);
        $ext   = null;
        $exts  = ['JR.', 'SR.', 'II', 'III', 'IV'];
        if (in_array(strtoupper($last), $exts)) {
            $ext  = $last;
            $last = array_pop($words);
        }
        $mi         = null;
        $firstParts = [];
        foreach ($words as $w) {
            if (strlen($w) <= 2 && str_ends_with($w, '.')) {
                $mi = rtrim($w, '.');
            } else {
                $firstParts[] = $w;
            }
        }
        return [$last, implode(' ', $firstParts), $mi, $ext];
    }
}
