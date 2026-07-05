<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PreevalReportExport implements FromCollection, WithEvents, ShouldAutoSize
{
    protected $groupedApplicants;

    public function __construct($groupedApplicants)
    {
        $this->groupedApplicants = $groupedApplicants;
    }

    public function collection()
    {
        // This is handled entirely in the AfterSheet event to allow multiple blocks 
        // with their own specific headers
        return collect([]); 
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $currentRow = 1;

                foreach ($this->groupedApplicants as $groupKey => $applicants) {
                    $firstApp = $applicants->first();
                    $position = mb_strtoupper($firstApp->position_applied);
                    $office = mb_strtoupper($firstApp->office ?: 'N/A');
                    $itemNos = $applicants->pluck('item_no')->filter()->unique()->implode(', ');
                    $sg = $firstApp->sg ?: '';

                    // Header Block
                    $sheet->mergeCells("A{$currentRow}:G{$currentRow}");
                    $sheet->setCellValue("A{$currentRow}", 'HUMAN RESOURCE MERIT PROMOTION AND SELECTION BOARD');
                    $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true)->setSize(12);
                    $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $currentRow++;

                    $sheet->mergeCells("A{$currentRow}:G{$currentRow}");
                    $sheet->setCellValue("A{$currentRow}", 'PRE-EVALUATION PASSED/FAILED ASSESSMENT MATRIX');
                    $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true)->setSize(11);
                    $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $currentRow += 2;

                    $sheet->setCellValue("A{$currentRow}", 'I. Evaluation Screening Tracker');
                    $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true);
                    $currentRow++;

                    // Info Block
                    $infoFields = [
                        ['Position Vacant:', $position],
                        ['Item Number(s):', $itemNos],
                        ['Salary Grade:', $sg],
                        ['Office Assignment:', $office]
                    ];

                    foreach ($infoFields as $info) {
                        $sheet->setCellValue("A{$currentRow}", $info[0]);
                        $sheet->mergeCells("B{$currentRow}:G{$currentRow}");
                        $sheet->setCellValue("B{$currentRow}", $info[1]);
                        
                        $sheet->getStyle("A{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F2');
                        $sheet->getStyle("A{$currentRow}:G{$currentRow}")->getFont()->setBold(true);
                        $sheet->getStyle("A{$currentRow}:G{$currentRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                        $currentRow++;
                    }

                    $currentRow++;

                    // Table Headers
                    $headers = [
                        'App. No.',
                        "Applicant Name\n(Last, First, M.I.)",
                        "Basic QS\nCompliance (Met / Unmet)",
                        "Examination Status\n(Passed / Failed / Absent)",
                        "Complete Documents\nSubmitted On-Time? (Yes / No)",
                        "FINAL RATING\n(QUALIFIED / DISQUALIFIED)",
                        'Remarks / Specific Grounds for Disqualification'
                    ];

                    $col = 'A';
                    foreach ($headers as $header) {
                        $sheet->setCellValue("{$col}{$currentRow}", $header);
                        $sheet->getStyle("{$col}{$currentRow}")->getAlignment()->setWrapText(true);
                        $col++;
                    }
                    
                    $sheet->getStyle("A{$currentRow}:G{$currentRow}")->getFont()->setBold(true);
                    $sheet->getStyle("A{$currentRow}:G{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF2F2F2');
                    $sheet->getStyle("A{$currentRow}:G{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("A{$currentRow}:G{$currentRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle("A{$currentRow}:G{$currentRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    
                    $currentRow++;

                    // Data Rows
                    foreach ($applicants as $index => $applicant) {
                        $name = mb_strtoupper($applicant->last_name) . ', ' . mb_strtoupper($applicant->first_name) . ' ' . mb_strtoupper(substr($applicant->middle_name, 0, 1)) . '.';
                        
                        $sheet->setCellValue("A{$currentRow}", $index + 1);
                        $sheet->setCellValue("B{$currentRow}", $name);
                        $sheet->setCellValue("C{$currentRow}", '');
                        $sheet->setCellValue("D{$currentRow}", '');
                        $sheet->setCellValue("E{$currentRow}", '');
                        $sheet->setCellValue("F{$currentRow}", '');
                        $sheet->setCellValue("G{$currentRow}", '');

                        $sheet->getStyle("A{$currentRow}:G{$currentRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                        $currentRow++;
                    }

                    $currentRow += 2;

                    // Sign-off
                    $sheet->setCellValue("A{$currentRow}", 'III. Sign-off and Endorsement');
                    $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true);
                    $currentRow++;

                    $sheet->setCellValue("A{$currentRow}", 'Evaluated and Certified True and Correct by:');
                    $sheet->getStyle("A{$currentRow}")->getFont()->setItalic(true);
                    $currentRow += 2;

                    $sheet->setCellValue("A{$currentRow}", 'THE PHRMO PRE-EVALUATION TEAM');
                    $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true);
                    $currentRow += 2;

                    $signatories = [
                        'P.G Assistant Department Head',
                        'HRMO IV',
                        'HRMO III'
                    ];

                    foreach ($signatories as $sig) {
                        $sheet->mergeCells("A{$currentRow}:C{$currentRow}");
                        $sheet->getStyle("A{$currentRow}:C{$currentRow}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
                        $currentRow++;
                        $sheet->setCellValue("A{$currentRow}", $sig);
                        $currentRow += 2;
                    }

                    $sheet->setCellValue("A{$currentRow}", 'Date Signed: ________________________');
                    $sheet->getStyle("A{$currentRow}")->getFont()->setItalic(true);
                    
                    $currentRow += 5; // Space before next group
                }
            },
        ];
    }
}
