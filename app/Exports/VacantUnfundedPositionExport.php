<?php

namespace App\Exports;

use App\Models\PlantillaRecord;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VacantUnfundedPositionExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles
{
    protected string $position;

    public function __construct(string $position)
    {
        $this->position = $position;
    }

    public function collection()
    {
        $records = PlantillaRecord::where(function($q) {
                $q->where(function($q2) {
                    $q2->where('is_vacant', true)
                       ->where(function($q3) {
                           $q3->where('abolished', true)->orWhere('dissolved', true);
                       });
                })->orWhere(function($q2) {
                    $q2->where(function($q3) {
                        $q3->whereNull('authorized_annual_salary')
                           ->orWhere('authorized_annual_salary', 0);
                    })->where(function($q3) {
                        $q3->whereNull('actual_annual_salary')
                           ->orWhere('actual_annual_salary', 0);
                    });
                });
            })
            ->where('position_title', $this->position)
            ->orderBy('organizational_unit')
            ->orderBy('item')
            ->get();

        return $records->map(fn ($r, $i) => [
            'No.'                      => $i + 1,
            'Item No.'                 => $r->item,
            'Position Title'           => $r->position_title,
            'Organizational Unit'      => $r->organizational_unit,
            'Salary Grade'             => $r->salary_grade,
            'Step'                     => $r->step,
            'Authorized Annual Salary' => number_format($r->authorized_annual_salary ?? 0, 2),
            'Area Code'                => $r->area_code,
            'Area Type'                => $r->area_type,
            'Level'                    => $r->level,
        ]);
    }

    public function headings(): array
    {
        return [
            'No.',
            'Item No.',
            'Position Title',
            'Organizational Unit',
            'Salary Grade',
            'Step',
            'Authorized Annual Salary',
            'Area Code',
            'Area Type',
            'Level',
        ];
    }

    public function title(): string
    {
        return 'Vacant Unfunded - ' . \Illuminate\Support\Str::limit($this->position, 28);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF6B7280']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }
}
