<?php

namespace App\Imports;

use App\Models\JobOrder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Carbon\Carbon;

/**
 * Job Order Inventory import.
 *
 * Expected columns (row 1 is the header, data starts row 2):
 *   A=CHARGES  B=LASTNAME  C=FIRSTNAME  D=M.I.  E=EXT.  F=POSITION
 *   G=NATURE OF WORK  H=OFFICE ASSIGNED  I=RATE/DAY
 *   J=FIRST DAY OF SERVICE  K=LENGTH YR/S (computed)  L=MONTH/S (computed)
 *   M=BIRTHDATE  N=STATUS  O=ADDRESS  P=ELIGIBILITY
 *   Q=NATURE OF WORK (detail)  R=GENDER  S=LEVEL
 *   T=IP COMMUNITY MEMBERSHIP  U=SOLO PARENT  V=REMARKS
 */
class JobOrderImport implements ToCollection, WithStartRow
{
    public array $errors   = [];
    public int   $imported = 0;
    public int   $skipped  = 0;

    /**
     * Data rows start at row 2 (after single header row).
     */
    public function startRow(): int
    {
        return 2; // actual Excel row number
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $rowIndex => $row) {
            $actualRow = $rowIndex + 2; // for error messages

            // Skip completely empty rows
            $rowArr = $row->toArray();
            if (empty(array_filter($rowArr, fn($v) => $v !== null && $v !== ''))) {
                $this->skipped++;
                continue;
            }

            // ── Column mapping (0-based) ─────────────────────────────────
            // 0  = CHARGES
            // 1  = LASTNAME (last_name)
            // 2  = FIRSTNAME (first_name)
            // 3  = M.I. (middle_initial)
            // 4  = EXT. (name_extension)
            // 5  = POSITION
            // 6  = NATURE OF WORK (category)
            // 7  = OFFICE ASSIGNED
            // 8  = RATE/DAY
            // 9  = FIRST DAY OF SERVICE
            // 10 = LENGTH OF SERVICE YEAR/S (computed – skip import)
            // 11 = MONTH/S (computed – skip import)
            // 12 = BIRTHDATE
            // 13 = STATUS (civil_status)
            // 14 = ADDRESS
            // 15 = ELIGIBILITY
            // 16 = NATURE OF WORK (detail / specific work type)
            // 17 = GENDER (M or F — single column)
            // 18 = LEVEL (M1, F1, M2, F2)
            // 19 = IP COMMUNITY MEMBERSHIP
            // 20 = SOLO PARENT
            // 21 = REMARKS

            $lastName  = $this->str($row, 1);
            $firstName = $this->str($row, 2);

            // Must have at least a last name
            if (empty($lastName)) {
                $this->skipped++;
                continue;
            }

            // Determine gender from single R column
            $genderRaw = strtoupper(trim((string) ($row->get(17) ?? '')));
            $gender = in_array($genderRaw, ['M', 'F']) ? $genderRaw : null;

            // Solo parent: check for truthy value
            $soloParent = $this->bool($row, 20);

            try {
                $joData = [
                    'charges'               => $this->str($row, 0),
                    'last_name'             => $lastName,
                    'first_name'            => $firstName,
                    'middle_initial'        => $this->str($row, 3),
                    'name_extension'        => $this->str($row, 4),
                    'position_title'        => $this->str($row, 5),
                    'nature_of_work'        => $this->str($row, 6),
                    'office'                => $this->str($row, 7),
                    'rate_per_day'          => $this->numeric($row, 8),
                    'first_day_of_service'  => $this->date($row, 9),
                    'birthdate'             => $this->date($row, 12),
                    'civil_status'          => $this->str($row, 13),
                    'address'               => $this->str($row, 14),
                    'eligibility'           => $this->str($row, 15),
                    'nature_of_work_detail' => $this->str($row, 16),
                    'gender'                => $gender,
                    'level'                 => $this->str($row, 18),
                    'ip_community_membership' => $this->str($row, 19),
                    'solo_parent'           => $soloParent,
                    'remarks'               => $this->str($row, 21),
                ];

                $existingJo = JobOrder::withTrashed()
                    ->where('first_name', $firstName)
                    ->where('last_name', $lastName)
                    ->first();
                    
                if ($existingJo) {
                    if ($existingJo->trashed()) $existingJo->restore();
                    $existingJo->update(array_filter($joData, fn($v) => $v !== null));
                } else {
                    JobOrder::create($joData);
                }

                // Sync data to ALL DATA (PlantillaRecord)
                $prData = [
                    'organizational_unit'       => $this->str($row, 7),
                    'last_name'                 => $lastName,
                    'first_name'                => $firstName,
                    'middle_name'               => $this->str($row, 3),
                    'position_title'            => $this->str($row, 5),
                    'date_original_appointment' => $this->date($row, 9),
                    'sex'                       => $gender,
                    'date_of_birth'             => $this->date($row, 12),
                    'civil_service_eligibility' => $this->str($row, 15),
                    'employment_status'         => 'JO',
                    'is_vacant'                 => false,
                    'nature_of_separation'      => null, // Clear separation if any
                    'date_separated'            => null,
                ];

                $existingPr = \App\Models\PlantillaRecord::withTrashed()
                    ->where('first_name', $firstName)
                    ->where('last_name', $lastName)
                    ->first();

                if ($existingPr) {
                    if ($existingPr->trashed()) $existingPr->restore();
                    $existingPr->update(array_filter($prData, fn($v) => $v !== null));
                } else {
                    $prData['item'] = null;
                    \App\Models\PlantillaRecord::create($prData);
                }

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

        // String date — handle YYYY/MM/DD or YYYY-MM-DD
        try {
            return Carbon::parse(str_replace('/', '-', $val))->format('Y-m-d');
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
