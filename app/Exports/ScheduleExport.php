<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ScheduleExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $grouped;

    public function __construct($grouped)
    {
        $this->grouped = $grouped;
    }

    public function view(): View
    {
        return view('step-increment.office-report-excel', [
            'grouped' => $this->grouped
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Center headers
            1    => ['font' => ['bold' => true]],
        ];
    }
}
