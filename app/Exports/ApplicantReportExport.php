<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;

class ApplicantReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithDrawings, WithEvents, WithCustomStartCell
{
    use \App\Exports\Traits\HasPhrmoHeader;

    protected $applicants;
    protected $startDate;
    protected $endDate;

    public function __construct($applicants, $startDate, $endDate)
    {
        $this->applicants = $applicants;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        return $this->applicants;
    }

    public function headings(): array
    {
        $period = \Carbon\Carbon::parse($this->startDate)->format('F j, Y') . ' - ' . \Carbon\Carbon::parse($this->endDate)->format('F j, Y');
        
        return [
            ['PROVINCIAL HUMAN RESOURCE MANAGEMENT OFFICE'],
            ['LIST OF APPLICANTS'],
            ['Period: ' . $period],
            [''],
            [
                'OFFICE',
                'VACANT POSITION',
                'ITEM NO.',
                'SG',
                'LIST OF APPLICANTS (Last Name, First Name, Middle Name)'
            ]
        ];
    }

    public function map($applicant): array
    {
        $fullName = mb_strtoupper($applicant->last_name) . ', ' . mb_strtoupper($applicant->first_name) . ' ' . mb_strtoupper($applicant->middle_name);

        return [
            $applicant->office ?: '-',
            $applicant->position_applied,
            $applicant->item_no ?: '-',
            $applicant->sg,
            $fullName,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // 1-6 are PhrmoHeader rows.
        // Title starts at 7, 8, 9, 10, Header is 11, Data starts 12.
        // Merge cells for the title
        $sheet->mergeCells('A7:E7');
        $sheet->mergeCells('A8:E8');
        $sheet->mergeCells('A9:E9');

        return [
            7  => ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            8  => ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            9  => ['font' => ['italic' => true, 'size' => 10], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
            11 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'DCE6F1']
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['argb' => '00000000'],
                    ],
                ]
            ],
        ];
    }
}
