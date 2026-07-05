<?php

namespace App\Exports\Traits;

use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

trait HasPhrmoHeader
{
    /**
     * Start the actual data table on row 7.
     */
    public function startCell(): string
    {
        return 'A7';
    }

    /**
     * Attach the logos to the Excel sheet.
     */
    public function drawings()
    {
        $drawings = [];

        // Left Logo - Provincial Seal
        if (file_exists(public_path('img/logo.png'))) {
            $drawingLeft = new Drawing();
            $drawingLeft->setName('Provincial Seal');
            $drawingLeft->setDescription('Provincial Seal');
            $drawingLeft->setPath(public_path('img/logo.png'));
            $drawingLeft->setHeight(70);
            $drawingLeft->setCoordinates('A1');
            $drawingLeft->setOffsetX(10);
            $drawingLeft->setOffsetY(5);
            $drawings[] = $drawingLeft;
        }

        // Right Logo - PHRMO
        if (file_exists(public_path('img/phrmologo.jpg'))) {
            $drawingRight = new Drawing();
            $drawingRight->setName('PHRMO Logo');
            $drawingRight->setDescription('PHRMO Logo');
            $drawingRight->setPath(public_path('img/phrmologo.jpg'));
            $drawingRight->setHeight(50);
            // Default to E1 if we don't know the exact width, but we can just use D1 or E1
            $drawingRight->setCoordinates('D1');
            $drawingRight->setOffsetX(10);
            $drawingRight->setOffsetY(10);
            $drawings[] = $drawingRight;
        } elseif (file_exists(public_path('img/phrmologo.png'))) {
            $drawingRight = new Drawing();
            $drawingRight->setName('PHRMO Logo');
            $drawingRight->setDescription('PHRMO Logo');
            $drawingRight->setPath(public_path('img/phrmologo.png'));
            $drawingRight->setHeight(50);
            $drawingRight->setCoordinates('D1');
            $drawingRight->setOffsetX(10);
            $drawingRight->setOffsetY(10);
            $drawings[] = $drawingRight;
        }

        return $drawings;
    }

    /**
     * Write the standard header text to the worksheet before the data starts.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Merge cells for the header text (columns A to E, adjust as needed)
                $sheet->mergeCells('A1:E1');
                $sheet->mergeCells('A2:E2');
                $sheet->mergeCells('A3:E3');
                $sheet->mergeCells('A4:E4');

                // Set text
                $sheet->setCellValue('A1', 'Republic of the Philippines');
                $sheet->setCellValue('A2', 'PROVINCE OF BUKIDNON');
                $sheet->setCellValue('A3', 'Provincial Capitol');
                $sheet->setCellValue('A4', 'PROVINCIAL HUMAN RESOURCE MANAGEMENT OFFICE');

                // Style text
                $sheet->getStyle('A1:A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A1:A4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('A1:A3')->getFont()->setSize(10);
                $sheet->getStyle('A4')->getFont()->setSize(11)->setBold(true);

                // Make rows a bit taller for the images
                $sheet->getRowDimension(1)->setRowHeight(20);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(3)->setRowHeight(20);
                $sheet->getRowDimension(4)->setRowHeight(20);
            },
        ];
    }
}
