<?php

namespace App\Services;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\SimpleType\JcTable;

class DocxGenerator
{
    public static function generateNosi($data, $bulk = false, $phpWord = null)
    {
        if (!$phpWord) {
            $phpWord = new PhpWord();
            $phpWord->setDefaultFontName('Times New Roman');
            $phpWord->setDefaultFontSize(11);
        }

        foreach (isset($data['records']) ? $data['records'] : [$data] as $record) {
            self::addNosiSection($phpWord, $record, 'NOSI');
        }

        return $phpWord;
    }

    public static function generateNolp($data, $bulk = false, $phpWord = null)
    {
        if (!$phpWord) {
            $phpWord = new PhpWord();
            $phpWord->setDefaultFontName('Times New Roman');
            $phpWord->setDefaultFontSize(11);
        }

        foreach (isset($data['records']) ? $data['records'] : [$data] as $record) {
            self::addNosiSection($phpWord, $record, 'NOLP');
        }

        return $phpWord;
    }

    private static function addNosiSection(PhpWord $phpWord, $data, $type = 'NOSI')
    {
        $employee = $data['employee'];
        $effectiveDate = $data['effectiveDate'];
        $currentSalary = $data['currentSalary'];
        $currentStep = $data['currentStep'];
        $newStep = $data['newStep'];
        $newSalary = $data['newSalary'];
        $diff = $data['diff'];

        $section = $phpWord->addSection([
            'marginTop' => Converter::cmToTwip(1.5),
            'marginLeft' => Converter::cmToTwip(2.0),
            'marginRight' => Converter::cmToTwip(2.0),
            'marginBottom' => Converter::cmToTwip(1.5),
        ]);

        $table = $section->addTable(['width' => 100 * 50, 'unit' => 'pct', 'alignment' => JcTable::CENTER]);
        $table->addRow();
        
        $cell1 = $table->addCell(2000, ['valign' => 'center']);
        if (file_exists(public_path('img/logo.png'))) {
            $cell1->addImage(public_path('img/logo.png'), ['width' => 60, 'height' => 60, 'alignment' => Jc::LEFT]);
        }
        
        $cell2 = $table->addCell(5000, ['valign' => 'center']);
        $cell2->addText('Republic of the Philippines', [], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $cell2->addText('PROVINCE OF BUKIDNON', ['bold' => true], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $cell2->addText('Malaybalay City', [], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
        $cell2->addText('OFFICE OF THE PROVINCIAL GOVERNOR', ['bold' => true, 'color' => '0F4C81', 'size' => 12], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);

        $cell3 = $table->addCell(2000, ['valign' => 'center']);
        if (file_exists(public_path('img/bagong-pilipinas.png'))) {
            $cell3->addImage(public_path('img/bagong-pilipinas.png'), ['width' => 60, 'height' => 60, 'alignment' => Jc::RIGHT]);
        }

        $section->addTextBreak(1);
        $section->addLine(['weight' => 2, 'width' => 500, 'height' => 0, 'color' => '000000']);
        $section->addTextBreak(1);
        
        $titleText = $type === 'NOSI' ? 'NOTICE OF STEP INCREMENT DUE TO LENGTH OF SERVICE' : 'NOTICE OF LONGEVITY PAY';
        $section->addText($titleText, ['bold' => true, 'size' => 12], ['alignment' => Jc::CENTER]);
        $section->addTextBreak(1);
        
        $section->addText(now()->format('F d, Y'), ['bold' => true], ['alignment' => Jc::RIGHT, 'spaceAfter' => 0]);
        $section->addText('Date', ['size' => 10], ['alignment' => Jc::RIGHT, 'spaceAfter' => 0]);
        $section->addTextBreak(1);

        $title = ($employee->sex == 'Female' || $employee->sex === 'F') ? 'Ms.' : 'Mr.';
        $empName = $title . ' ' . strtoupper($employee->first_name . ' ' . ($employee->middle_name ? substr($employee->middle_name, 0, 1) . '. ' : '') . $employee->last_name);
        
        $location = 'Malaybalay City, Bukidnon';
        if ($type === 'NOLP') {
            $ou = strtoupper($employee->organizational_unit ?? '');
            $locationMap = [
                'KIBAWE' => 'Kibawe, Bukidnon',
                'MARAMAG' => 'Maramag, Bukidnon',
                'MALAYBALAY' => 'Malaybalay City, Bukidnon',
                'MANOLO' => 'Manolo Fortich, Bukidnon',
                'QUEZON' => 'Quezon, Bukidnon',
                'TALAKAG' => 'Talakag, Bukidnon',
                'CABANGLASAN' => 'Cabanglasan, Bukidnon',
                'WAO' => 'Wao, Lanao del Sur',
            ];
            foreach ($locationMap as $key => $city) {
                if (str_contains($ou, $key)) {
                    $location = $city;
                    break;
                }
            }
        }

        $section->addText($empName, ['bold' => true, 'underline' => 'single'], ['spaceAfter' => 0]);
        $section->addText($employee->organizational_unit, ['bold' => true], ['spaceAfter' => 0]);
        $section->addText($location, ['bold' => true, 'underline' => 'single'], ['spaceAfter' => 0]);
        $section->addTextBreak(1);

        $textRun = $section->addTextRun(['spaceAfter' => 0]);
        $textRun->addText('Dear ');
        $textRun->addText($title . ' ' . explode(' ', $employee->last_name)[0] . ':', ['bold' => true]);
        $section->addTextBreak(1);

        $textRun = $section->addTextRun(['alignment' => Jc::BOTH, 'lineHeight' => 1.5]);
        if ($type === 'NOSI') {
            $textRun->addText("Pursuant to the Civil Service Commission and Department of Budget and Management Joint Circular No. 1 dated September 3, 2012, implementing item (4)(d) of the Senate and House of Representatives Joint Resolution No. 4, s. 2009, approved on June 17, 2009, your salary as ", [], ['indentation' => ['firstLine' => 720]]);
        } else {
            $textRun->addText("Pursuant to the Department of Health and Department of Budget and Management Joint Circular No. 1 dated November 29, 2012, implementing item (4)(d) of the Senate and House of Representatives Joint Resolution No. 4, s. 2009, approved on June 17, 2009, your salary as ", [], ['indentation' => ['firstLine' => 720]]);
        }
        $textRun->addText($employee->position_title, ['bold' => true, 'underline' => 'single']);
        $textRun->addText(" is hereby adjusted effective ");
        $textRun->addText($effectiveDate->format('F j, Y'), ['bold' => true, 'underline' => 'single']);
        $textRun->addText(", as follows:");

        $section->addTextBreak(1);

        $compTable = $section->addTable(['cellMarginLeft' => 720]);
        $compTable->addRow();
        $cell = $compTable->addCell(6000);
        $cell->addText('1. Actual monthly basic salary as of ' . $effectiveDate->clone()->subDay()->format('F j, Y'), [], ['spaceAfter' => 0]);
        $cell->addText("    (SG - {$employee->salary_grade}, Step {$currentStep})", [], ['spaceAfter' => 0]);
        $cell = $compTable->addCell(3000, ['valign' => 'bottom']);
        $cell->addText('P ' . number_format($currentSalary, 2), ['bold' => true], ['spaceAfter' => 0]);

        $compTable->addRow();
        $cell = $compTable->addCell(6000);
        if ($type === 'NOSI') {
            $cell->addText('2. Add: One (1) Step Increment', [], ['spaceAfter' => 0]);
        } else {
            $cell->addText('2. Add: Two (2) Step Increment', [], ['spaceAfter' => 0]);
        }
        $cell->addText("    Due to Length of Service; (SG - {$employee->salary_grade}, Step {$newStep})", [], ['spaceAfter' => 0]);
        $cell = $compTable->addCell(3000, ['valign' => 'bottom']);
        $cell->addText('P ' . number_format($diff, 2), ['bold' => true], ['spaceAfter' => 0]);

        $compTable->addRow();
        $cell = $compTable->addCell(6000);
        $cell->addText('3. Adjusted monthly basic salary effective ' . $effectiveDate->format('F j, Y'), [], ['spaceAfter' => 0]);
        $cell = $compTable->addCell(3000, ['valign' => 'bottom']);
        $cell->addText('P ' . number_format($newSalary, 2), ['bold' => true], ['spaceAfter' => 0]);

        $section->addTextBreak(1);
        $section->addText('This salary adjustment is subject to review and post-audit, and to appropriate re-adjustment and refund if found not in order.', [], ['indentation' => ['firstLine' => 720], 'lineHeight' => 1.5]);
        
        $section->addTextBreak(2);
        $section->addText('Very truly yours,', [], ['indentation' => ['left' => 5000]]);
        $section->addTextBreak(2);
        $section->addText('ROGELIO NEIL P. ROQUE', ['bold' => true], ['indentation' => ['left' => 5000], 'spaceAfter' => 0]);
        $section->addText('Provincial Governor', [], ['indentation' => ['left' => 5000]]);

        $section->addTextBreak(2);
        $section->addText("Item No. {$employee->item} / Unique Item No. ______", [], ['spaceAfter' => 0]);
        $section->addText("FY " . now()->year . " Personal Services Itemization and/or", [], ['spaceAfter' => 0]);
        $section->addText("Plantilla of Personnel", [], ['spaceAfter' => 0]);
        $section->addText("CF: GSIS", ['size' => 9.5]);

        $section->addTextBreak(1);
        $section->addLine(['weight' => 1, 'width' => 500, 'height' => 0, 'color' => '000000']);
        $section->addText("Tel. No.: (088) 537-4813    |    Email address: governor@bukidnon.gov.ph", ['size' => 9.5]);
    }

    public static function download(PhpWord $phpWord, $filename)
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'phpword');
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempFile);
        
        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }
}
