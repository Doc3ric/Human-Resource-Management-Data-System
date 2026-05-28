<?php

namespace App\Exports;

use App\Models\PlantillaRecord;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PwdExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    public function collection()
    {
        return PlantillaRecord::where('is_pwd', true)
            ->where('is_vacant', false)
            ->orderBy('organizational_unit')
            ->orderBy('last_name')
            ->get()
            ->map(fn ($r, $i) => [
                'No.'                  => $i + 1,
                'Item No.'             => $r->item,
                'Last Name'            => strtoupper($r->last_name ?? ''),
                'First Name'           => $r->first_name ?? '',
                'Middle Name'          => $r->middle_name ?? '',
                'Type of Disability'   => $r->type_of_disability ?? 'Unspecified',
                'Office / Unit'        => $r->organizational_unit,
                'Position Title'       => $r->position_title,
                'Appointment Status'   => strtoupper($r->employment_status ?? ''),
                'Salary Grade'         => $r->salary_grade,
            ]);
    }

    public function headings(): array
    {
        return [
            'No.', 'Item No.', 'Last Name', 'First Name', 'Middle Name',
            'Type of Disability', 'Office / Unit', 'Position Title',
            'Appointment Status', 'Salary Grade',
        ];
    }

    public function title(): string
    {
        return 'PWD Personnel Report';
    }
}
