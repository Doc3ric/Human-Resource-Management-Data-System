<?php

namespace App\Exports;

use App\Models\PlantillaRecord;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class VacantExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithDrawings, WithEvents, WithCustomStartCell
{
    use \App\Exports\Traits\HasPhrmoHeader;

    protected string $type;
    protected ?string $office;

    public function __construct(string $type = 'funded', ?string $office = null)
    {
        $this->type = $type;
        $this->office = $office;
    }

    public function collection()
    {
        if ($this->type === 'unfunded') {
            $records = PlantillaRecord::where(function ($q) {
                $q->where(function ($q2) {
                    $q2->where('is_vacant', true)
                       ->where(function ($q3) { $q3->where('abolished', true)->orWhere('dissolved', true); });
                })->orWhere(function ($q2) {
                    $q2->where(function ($q3) {
                        $q3->whereNull('authorized_annual_salary')->orWhere('authorized_annual_salary', 0);
                    })->where(function ($q3) {
                        $q3->whereNull('base_salary_amount')->orWhere('base_salary_amount', 0);
                    });
                });
            })->when($this->office, fn ($q) => $q->where('office_department', $this->office))
                ->orderBy('position_title')->get();
        } else {
            $records = PlantillaRecord::where('is_vacant', true)
                ->where('abolished', false)
                ->where('dissolved', false)
                ->when($this->office, fn ($q) => $q->where('office_department', $this->office))
                ->orderBy('position_title')
                ->get();
        }

        return $records->map(fn ($r, $i) => [
            'No.'                   => $i + 1,
            'Item No.'              => $r->item_no_new,
            'Position Title'        => $r->position_title,
            'Office / Unit'         => $r->office_department,
            'Salary Grade'          => $r->salary_grade,
            'Step'                  => $r->step,
            'Authorized Salary'     => number_format($r->authorized_annual_salary ?? 0, 2),
            'Abolished / Dissolved' => ($r->abolished ? 'Abolished' : ($r->dissolved ? 'Dissolved' : 'No')),
        ]);
    }

    public function headings(): array
    {
        return ['No.', 'Item No.', 'Position Title', 'Office / Unit', 'Salary Grade', 'Step', 'Authorized Annual Salary', 'Abolished / Dissolved'];
    }

    public function title(): string
    {
        // Excel sheet titles are capped at 31 chars; keep it short even when office-scoped.
        return 'Vacant ' . ucfirst($this->type);
    }
}
