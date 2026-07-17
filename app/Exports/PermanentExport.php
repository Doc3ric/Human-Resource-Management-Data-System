<?php

namespace App\Exports;

use App\Models\PlantillaRecord;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PermanentExport implements FromView, WithTitle, ShouldAutoSize, WithStyles, WithEvents, WithDrawings, WithCustomStartCell
{
    use \App\Exports\Traits\HasPhrmoHeader;

    protected array $filters;
    protected array $columns;

    private const STATUSES = ['P', 'Permanent', 'CT', 'Co-Terminous', 'Coterminous', 'E', 'Elected'];

    public function __construct(array $filters = [], array $columns = [])
    {
        $this->filters = $filters;
        $this->columns = $columns;
    }

    public function title(): string
    {
        return 'Permanent Employees';
    }

    public function view(): View
    {
        $query = PlantillaRecord::whereIn('employment_status', self::STATUSES)
            ->orderBy('office_department')
            ->orderBy('last_name');

        if (!empty($this->filters['search'])) {
            $term = $this->filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('last_name',             'like', "%{$term}%")
                  ->orWhere('first_name',           'like', "%{$term}%")
                  ->orWhere('item_no_new',                 'like', "%{$term}%")
                  ->orWhere('position_title',       'like', "%{$term}%")
                  ->orWhere('office_department',  'like', "%{$term}%");
            });
        }

        if (!empty($this->filters['office'])) {
            $query->where('office_department', 'like', '%' . $this->filters['office'] . '%');
        }

        if (!empty($this->filters['sex'])) {
            $query->where('sex', $this->filters['sex']);
        }

        if (!empty($this->filters['vacant'])) {
            $query->where('is_vacant', $this->filters['vacant'] === 'vacant');
        }

        $statusFilter = $this->filters['status_filter'] ?? 'active';
        if ($statusFilter === 'active')       $query->whereNull('nature_of_separation');
        elseif ($statusFilter === 'inactive') $query->whereNotNull('nature_of_separation');

        $records = $query->get();
        $columns = $this->columns;

        return view('exports.all-data-excel', compact('records', 'columns'));
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $sheet->getHighestRow();

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 9],
            ],
            "A2:W{$lastRow}" => [
                'font'      => ['size' => 8],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->insertNewRowBefore(1, 2);

                $lastRow = $sheet->getHighestRow();

                $sheet->freezePane('A4');

                $totalCols   = empty($this->columns) ? 17 : count($this->columns) + 1;
                $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalCols);

                // Row 1: Title
                $sheet->mergeCells("A1:{$lastColLetter}1");
                $sheet->setCellValue('A1', 'CSC PLANTILLA — Permanent Employees Report');
                $sheet->getStyle('A1')->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FF312E81']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(22);

                // Row 2: Date
                $sheet->mergeCells("A2:{$lastColLetter}2");
                $sheet->setCellValue('A2', 'Generated: ' . now()->format('F d, Y h:i A'));
                $sheet->getStyle('A2')->applyFromArray([
                    'font'      => ['size' => 9, 'italic' => true, 'color' => ['argb' => 'FF6B7280']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(16);

                // Row 3: Header
                $sheet->getStyle("A3:{$lastColLetter}3")->applyFromArray([
                    'font'      => ['bold' => true, 'color' => ['argb' => 'FF000000'], 'size' => 9],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
                ]);
                $sheet->getRowDimension(3)->setRowHeight(30);

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
                $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 3);
            },
        ];
    }
}
