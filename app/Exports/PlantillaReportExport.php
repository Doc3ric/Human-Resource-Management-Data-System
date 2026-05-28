<?php

namespace App\Exports;

use App\Models\SalaryGrade;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;

class PlantillaReportExport implements FromView, WithTitle, ShouldAutoSize
{
    protected array $grouped;
    protected ?string $officeName;

    /**
     * @param array       $grouped     Office => records[]
     * @param string|null $officeName  For single-office exports
     */
    public function __construct(array $grouped, ?string $officeName = null)
    {
        $this->grouped    = $grouped;
        $this->officeName = $officeName;
    }

    public function view(): View
    {
        return view('step-increment.plantilla-report-excel', [
            'grouped'    => $this->grouped,
            'officeName' => $this->officeName,
        ]);
    }

    public function title(): string
    {
        return $this->officeName
            ? substr(preg_replace('/[^a-zA-Z0-9 ]/', '', $this->officeName), 0, 31)
            : 'Plantilla Report';
    }
}
