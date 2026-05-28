<?php

namespace App\Imports;

use App\Models\JobOrder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Carbon\Carbon;

/**
 * Job Order Inventory import.
 *
 * Expected columns (row 9 is the sub-header, data starts row 10):
 *   NO. | CHARGES | FAMILY | FIRST | M.I. | EXT | POSITION | NATURE OF WORK |
 *   OFFICE | RATE/DAY | FIRST DAY OF SERVICE | BIRTHDATE | ADDRESS |
 *   ELIGIBILITY | GENDER (M col) | GENDER (F col) |
 *   1st LEVEL | 2nd LEVEL | IP COMMUNITY MEMBERSHIP | SOLO PARENT | REMARKS
 *
 * We skip the heading rows manually and use column indices.
 */
class JobOrderImport implements ToCollection, WithStartRow
{
    public array $errors  = [];
    public int   $imported = 0;
    public int   $skipped  = 0;

    /**
     * Data rows start at row 10 (0-indexed internally = row index 9).
     * The sheet has merged header rows 1-8 then column labels at row 9.
     */
    public function startRow(): int
    {
        return 10; // actual Excel row number
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $rowIndex => $row) {
            $actualRow = $rowIndex + 10; // for error messages

            // Skip completely empty rows
            $rowArr = $row->toArray();
            if (empty(array_filter($rowArr, fn($v) => $v !== null && $v !== ''))) {
                $this->skipped++;
                continue;
            }

            // ── Column mapping (0-based) ─────────────────────────────────
            // 0  = NO.
            // 1  = CHARGES
            // 2  = FAMILY (last_name)
            // 3  = FIRST (first_name)
            // 4  = M.I. (middle_initial)
            // 5  = EXT (name_extension)
            // 6  = POSITION
            // 7  = NATURE OF WORK
            // 8  = OFFICE
            // 9  = RATE/DAY
            // 10 = FIRST DAY OF SERVICE
            // 11 = LENGTH YRS (computed – skip)
            // 12 = LENGTH MOS (computed – skip)
            // 13 = BIRTHDATE
            // 14 = ADDRESS
            // 15 = ELIGIBILITY
            // 16 = GENDER M  (if filled → 'M')
            // 17 = GENDER F  (if filled → 'F')
            // 18 = 1st LEVEL (checkmark → bool)
            // 19 = 2nd LEVEL (checkmark → bool)
            // 20 = IP COMMUNITY MEMBERSHIP
            // 21 = SOLO PARENT (checkmark → bool)
            // 22 = REMARKS

            $lastName  = $this->str($row, 2);
            $firstName = $this->str($row, 3);

            // Must have at least a last name
            if (empty($lastName)) {
                $this->skipped++;
                continue;
            }

            // Determine gender from two separate M/F columns using checkmarks
            $gender = null;
            if ($this->bool($row, 16)) $gender = 'M';
            elseif ($this->bool($row, 17)) $gender = 'F';

            try {
                JobOrder::create([
                    'charges'                  => $this->str($row, 1),
                    'last_name'                => $lastName,
                    'first_name'               => $firstName,
                    'middle_initial'           => $this->str($row, 4),
                    'name_extension'           => $this->str($row, 5),
                    'position_title'           => $this->str($row, 6),
                    'nature_of_work'           => $this->str($row, 7),
                    'office'                   => $this->str($row, 8),
                    'rate_per_day'             => $this->numeric($row, 9),
                    'first_day_of_service'     => $this->date($row, 10),
                    'birthdate'                => $this->date($row, 13),
                    'address'                  => $this->str($row, 14),
                    'eligibility'              => $this->str($row, 15),
                    'gender'                   => $gender,
                    'first_level_eligibility'  => $this->bool($row, 18),
                    'second_level_eligibility' => $this->bool($row, 19),
                    'ip_community_membership'  => $this->str($row, 20),
                    'solo_parent'              => $this->bool($row, 21),
                    'remarks'                  => $this->str($row, 22),
                ]);

                // Sync data to ALL DATA (PlantillaRecord)
                \App\Models\PlantillaRecord::create([
                    'organizational_unit'       => $this->str($row, 8),
                    'last_name'                 => $lastName,
                    'first_name'                => $firstName,
                    'middle_name'               => $this->str($row, 4),
                    'position_title'            => $this->str($row, 6),
                    'date_original_appointment' => $this->date($row, 10),
                    'sex'                       => $gender,
                    'date_of_birth'             => $this->date($row, 13),
                    'civil_service_eligibility' => $this->str($row, 15),
                    'employment_status'         => 'JO',
                    'is_vacant'                 => false,
                    'item'                      => null,
                ]);

                $this->imported++;
            } catch (\Throwable $e) {
                $this->errors[] = "Row {$actualRow}: " . $e->getMessage();
            }
        }
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    private function str(Collection $row, int $col): ?string
    {
        $val = $row->get($col);
        return ($val !== null && $val !== '') ? trim((string) $val) : null;
    }

    private function numeric(Collection $row, int $col): ?float
    {
        $val = $row->get($col);
        if ($val === null || $val === '') return null;
        
        // Remove commas from numbers like 1,250.00
        $valStr = str_replace(',', '', trim((string) $val));
        
        return is_numeric($valStr) ? (float) $valStr : null;
    }

    private function date(Collection $row, int $col): ?string
    {
        $val = $row->get($col);
        if ($val === null || $val === '') return null;

        // Excel serial number
        if (is_numeric($val)) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($val))->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        // String date
        try {
            return Carbon::parse($val)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function bool(Collection $row, int $col): bool
    {
        $val = $row->get($col);
        if ($val === null || $val === '') return false;
        $str = strtolower(trim((string) $val));
        return in_array($str, ['1', 'yes', 'true', '✓', 'x', 'checked', '/'], true);
    }
}
