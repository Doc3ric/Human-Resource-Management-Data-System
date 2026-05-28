<?php

namespace App\Exports\Reports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class Report2Export implements FromView, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function title(): string
    {
        return 'Report 2 - By Status';
    }

    public function view(): View
    {
        return view('exports.reports.report2-excel', $this->data);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 9]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastCol = 'E';
                $lastRow = $sheet->getHighestRow();

                $sheet->insertNewRowBefore(1, 2);
                $sheet->freezePane('A4');

                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'Inventory of Personnel by Appointment Status — Gender & Age Profile');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FF0F2942']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(20);

                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'Period: ' . \Carbon\Carbon::createFromDate($this->data['year'], $this->data['month'], 1)->format('F Y') . '   |   Generated: ' . now()->format('F d, Y h:i A'));
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['size' => 9, 'italic' => true, 'color' => ['argb' => 'FF6B7280']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(15);

                $sheet->getStyle("A3:{$lastCol}3")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 9],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
                    'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE2E8F0']],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(28);

                $sheet->getStyle("A3:{$lastCol}{$lastRow}")->getBorders()->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                    ->getColor()->setARGB('FF000000');

                $sheet->getPageSetup()
                    ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT)
                    ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
                    ->setFitToPage(true)->setFitToWidth(1)->setFitToHeight(0);
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 3);
            },
        ];
    }
}
