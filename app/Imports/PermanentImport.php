<?php

namespace App\Imports;

use App\Models\PlantillaRecord;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Carbon\Carbon;

/**
 * Permanent/Co-Terminous/Elected Import — simple-template format only
 * (PermanentController::downloadTemplate()). Column layout matches the
 * Casual simple template with one Employment Status column prepended,
 * since this module spans three statuses instead of one fixed status.
 *
 * Deliberately does not attempt the native "Plantilla of Personnel" LBP
 * Form 3 auto-detect that CasualImport supports for bulk province-wide
 * reconciliation — that's a much larger, already-solved problem handled by
 * the dedicated `reg3rd:apply`-style reconciliation tooling. This importer
 * is for ad-hoc single-office/single-batch adds via the simple template.
 */
class PermanentImport implements ToCollection
{
    public array $errors  = [];
    public int   $imported = 0;
    public int   $skipped  = 0;
    public array $createdIds = [];

    private const STATUSES = ['P', 'Permanent', 'CT', 'Co-Terminous', 'Coterminous', 'E', 'Elected'];
    private const STATUS_MAP = ['P' => 'P', 'CT' => 'CT', 'E' => 'E'];

    /**
     * Simple template columns (0-indexed):
     * 0=STATUS, 1=OFFICE, 2=ITEM_OLD, 3=ITEM_NEW, 4=POSITION, 5=LAST_NAME,
     * 6=FIRST_NAME, 7=MI, 8=EXT, 9=VACANT(Y), 10=SG_CUR, 11=STEP_CUR,
     * 12=SALARY_CUR, 13=SG_PROP, 14=STEP_PROP, 15=SALARY_PROP, 16=INCREASE,
     * 17=PREV_RATE, 18=CUR_RATE, 19=SEX, 20=BIRTHDATE, 21=FIRST_DAY, 22=ELIGIBILITY
     */
    public function collection(Collection $rows): void
    {
        $headerIndex = $this->findHeaderRowIndex($rows);
        $dataRows = $headerIndex !== null ? $rows->slice($headerIndex + 1) : $rows->slice(2);

        foreach ($dataRows as $rowIndex => $row) {
            $actualRow = $rowIndex + 1;

            $rowArr = $row->toArray();
            if (empty(array_filter($rowArr, fn ($v) => $v !== null && $v !== ''))) {
                $this->skipped++;
                continue;
            }

            $lastName = $this->str($row, 5);
            $isVacant = strtoupper($this->str($row, 9) ?? '') === 'Y' || strtoupper($lastName ?? '') === 'VACANT';

            if (!$isVacant && empty($lastName)) {
                $this->skipped++;
                continue;
            }

            $statusRaw = strtoupper($this->str($row, 0) ?? 'P');
            $status = self::STATUS_MAP[$statusRaw] ?? 'P';

            try {
                $data = [
                    'employment_status'        => $status,
                    'office_department'        => $this->str($row, 1),
                    'item_no_old'              => $this->str($row, 2),
                    'item_no_new'              => $this->str($row, 3) ?? $this->str($row, 2),
                    'position_title'           => $this->str($row, 4),
                    'last_name'                => $isVacant ? null : $lastName,
                    'first_name'               => $isVacant ? null : $this->str($row, 6),
                    'middle_name'              => $isVacant ? null : $this->str($row, 7),
                    'name_extension'           => $isVacant ? null : $this->str($row, 8),
                    'is_vacant'                => $isVacant,
                    'salary_grade'             => $this->int($row, 10),
                    'step'                     => $this->int($row, 11),
                    'authorized_annual_salary' => $this->numeric($row, 12),
                    'sg_proposed'              => $this->int($row, 13),
                    'step_proposed'            => $this->int($row, 14),
                    'salary_proposed'          => $this->numeric($row, 15),
                    'increase_decrease'        => $this->numeric($row, 16),
                    'previous_rate'            => $this->numeric($row, 17),
                    'base_salary_amount'       => $this->numeric($row, 18),
                    'sex'                      => $this->gender($row, 19),
                    'date_of_birth'            => $this->date($row, 20),
                    'first_day_of_service'     => $this->date($row, 21),
                    'civil_service_eligibility' => $this->str($row, 22),
                ];

                if ($isVacant || empty($data['first_name'])) {
                    $record = PlantillaRecord::create($data);
                } else {
                    $existing = PlantillaRecord::withTrashed()
                        ->whereIn('employment_status', self::STATUSES)
                        ->where('first_name', $data['first_name'])
                        ->where('last_name', $data['last_name'])
                        ->first();

                    if ($existing) {
                        if ($existing->trashed()) {
                            $existing->restore();
                        }
                        $existing->update(array_filter($data, fn ($v) => $v !== null));
                        $record = $existing;
                    } else {
                        $record = PlantillaRecord::create($data);
                    }
                }

                $this->createdIds[] = $record->id;
                $this->imported++;
            } catch (\Throwable $e) {
                $this->errors[] = "Row {$actualRow}: " . $e->getMessage();
            }
        }
    }

    private function findHeaderRowIndex(Collection $rows): ?int
    {
        foreach ($rows->take(20) as $i => $row) {
            $joined = strtoupper(implode(' ', array_map('strval', $row->toArray())));
            if (str_contains($joined, 'LAST NAME') && str_contains($joined, 'FIRST NAME')) {
                return $i;
            }
        }
        return null;
    }

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
        $val = strtoupper(trim((string) ($row->get($col) ?? '')));
        return in_array($val, ['M', 'F']) ? $val : null;
    }

    private function date(Collection $row, int $col): ?string
    {
        $val = $row->get($col);
        if ($val === null || $val === '') return null;
        if (is_numeric($val)) {
            try {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($val))->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }
        try {
            return Carbon::parse($val)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
