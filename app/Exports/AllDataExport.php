<?php

namespace App\Exports;

use App\Models\PlantillaRecord;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Font;

class AllDataExport implements FromView, WithTitle, ShouldAutoSize, WithStyles, WithEvents
{
    protected $filters;
    protected $columns;

    public function __construct(array $filters = [], array $columns = [])
    {
        $this->filters = $filters;
        $this->columns = $columns;
    }

    public function title(): string
    {
        return 'All Plantilla Data';
    }

    public function view(): View
    {
        $query = PlantillaRecord::query()
            ->orderBy('organizational_unit')
            ->orderBy('item');

        if (!empty($this->filters['search'])) {
            $term = $this->filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('last_name', 'like', "%{$term}%")
                    ->orWhere('first_name', 'like', "%{$term}%")
                    ->orWhere('item', 'like', "%{$term}%")
                    ->orWhere('position_title', 'like', "%{$term}%")
                    ->orWhere('organizational_unit', 'like', "%{$term}%")
                    ->orWhere('tin', 'like', "%{$term}%");
            });
        }

        if (!empty($this->filters['office'])) {
            $query->where('organizational_unit', $this->filters['office']);
        }

        if (!empty($this->filters['status'])) {
            $statusMap = [
                'P' => ['P', 'Permanent'],
                'CT' => ['CT', 'Co-Terminous', 'Coterminous'],
                'E' => ['E', 'Elected'],
                'Casual' => ['Casual', 'Cas'],
                'JO' => ['JO', 'Job Order', 'J.O.'],
            ];
            $s = $this->filters['status'];
            if (isset($statusMap[$s])) {
                $query->whereIn('employment_status', $statusMap[$s]);
            }
        }

        if (!empty($this->filters['sex'])) {
            $query->where('sex', $this->filters['sex']);
        }

        $records = $query->get();
        $columns = $this->columns;

        return view('exports.all-data-excel', compact('records', 'columns'));
    }

    public function styles(Worksheet $sheet): array
    {
        // styles() runs before AfterSheet, so the HTML is in row 1 onwards.
        // We apply data-row font here; header styling is done in AfterSheet
        // AFTER insertNewRowBefore pushes everything to the correct position.
        $lastRow = $sheet->getHighestRow();
        $lastCol = $sheet->getHighestColumn(); // dynamic — covers all selected columns

        return [
            // Row 1 (HTML thead) – will be moved to row 3 in AfterSheet
            1 => [
                'font' => ['bold' => true, 'size' => 9],
            ],
            // Data rows (will become rows 4+ after AfterSheet inserts 2 rows)
            "A2:{$lastCol}{$lastRow}" => [
                'font' => ['size' => 8],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // ── Insert 2 blank rows at the top so the HTML header row
                //    (originally row 1) is pushed down to row 3 ──────────
                $sheet->insertNewRowBefore(1, 2);

                $lastRow = $sheet->getHighestRow();

                // Freeze top 3 rows (title + date + header)
                $sheet->freezePane('A4');

                // Calculate the last column letter dynamically
                // 31 = full column set: ORG UNIT, ITEM, POSITION TITLE, SG,
                //   AUTH SAL, ACT SAL, STEP, AREA CODE, AREA TYPE, LEVEL, LAST NAME, FIRST NAME,
                //   MIDDLE NAME, SEX, RELIGION, DOB, TIN, DATE OA, DATE LP, STATUS, CS ELIGIBILITY,
                //   PWD, COMMENT/ANNOTATION, TERMINATION, INDIGENOUS PEOPLE, SOLO PARENT,
                //   ABOLISHED, DISSOLVED, GSIS BP NUMBER, POSITION CLASSIFICATION, EMPLOYEE NO.
                $totalCols = empty($this->columns) ? 31 : count($this->columns);
                $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);

                // Row 1: Report title
                $sheet->mergeCells("A1:{$lastColLetter}1");
                $sheet->setCellValue('A1', 'CSC PLANTILLA — Complete Data Report');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FF0F2942']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(22);

                // Row 2: Generated date
                $sheet->mergeCells("A2:{$lastColLetter}2");
                $sheet->setCellValue('A2', 'Generated: ' . now()->format('F d, Y h:i A'));
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['size' => 9, 'italic' => true, 'color' => ['argb' => 'FF6B7280']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(16);

                // Row 3: Header row (now correctly placed after insert)
                $sheet->getStyle("A3:{$lastColLetter}3")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FF000000'], 'size' => 9],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(30);

                // Alternate row shading (data starts at row 4) is removed for clean printing
    
                // Borders
                $sheet->getStyle("A3:{$lastColLetter}{$lastRow}")->getBorders()->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                    ->getColor()->setARGB('FF000000');

                // Print settings
                $sheet->getPageSetup()->setOrientation(
                    \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
                );
                $sheet->getPageSetup()->setPaperSize(
                    \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4
                );
                $sheet->getPageSetup()->setFitToPage(true);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                // Print header on every page
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 3);
            },
        ];
    }
}
