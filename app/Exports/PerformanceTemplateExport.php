<?php

namespace App\Exports;

use App\Models\PlantillaRecord;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PerformanceTemplateExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $office;
    protected $year;
    protected $period_type;
    protected $custom_period;

    public function __construct($office, $year, $period_type, $custom_period = null)
    {
        $this->office = $office;
        $this->year = $year;
        $this->period_type = $period_type;
        $this->custom_period = $custom_period;
    }

    public function collection()
    {
        return PlantillaRecord::where('office_department', $this->office)
            ->where('is_vacant', false)
            ->with(['ipcrRatings' => function($q) {
                $q->where('year', $this->year)
                  ->where('period_type', $this->period_type);
                if ($this->period_type === 'custom' && $this->custom_period) {
                    $q->where('custom_period', $this->custom_period);
                }
            }])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Employee ID',
            'Employee Name (Read-Only)',
            'Target Submitted (Yes/No)',
            'Target Date (YYYY-MM-DD)',
            'Rating Score',
            'Rating Date (YYYY-MM-DD)'
        ];
    }

    public function map($employee): array
    {
        $rating = $employee->ipcrRatings->first();

        return [
            $employee->id,
            $employee->full_name,
            $rating ? ($rating->target_submitted ? 'Yes' : 'No') : 'No',
            $rating && $rating->target_submission_date ? $rating->target_submission_date->format('Y-m-d') : '',
            $rating ? $rating->rating : '',
            $rating && $rating->rating_submission_date ? $rating->rating_submission_date->format('Y-m-d') : '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Make the first row bold and highlight the Employee ID column as important
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->getStyle('A1:A' . $sheet->getHighestRow())
            ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFFDF0ED'); // subtle red to indicate do not touch
            
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }
}
