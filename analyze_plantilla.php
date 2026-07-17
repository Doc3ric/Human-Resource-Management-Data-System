<?php

use PhpOffice\PhpSpreadsheet\IOFactory;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$filePath = __DIR__.'/PGB-PLANTILLA-2025.xlsx';

if (!file_exists($filePath)) {
    echo "File not found: $filePath\n";
    exit(1);
}

try {
    $spreadsheet = IOFactory::load($filePath);
    $sheets = $spreadsheet->getSheetNames();
    
    echo "Found " . count($sheets) . " sheets:\n";
    foreach ($sheets as $idx => $sheetName) {
        echo "- $sheetName\n";
        if ($idx == 0) {
            $sheet = $spreadsheet->getSheet($idx);
            $data = $sheet->toArray(null, true, true, true);
            echo "  Preview of first sheet (first 10 rows):\n";
            $rowLimit = 0;
            foreach ($data as $rowIndex => $row) {
                if ($rowLimit++ > 10) break;
                // Only print non-empty columns to keep it concise
                $filteredRow = array_filter($row, fn($val) => $val !== null && $val !== '');
                echo "  Row $rowIndex: " . json_encode($filteredRow) . "\n";
            }
        }
    }
} catch (\Exception $e) {
    echo "Error reading file: " . $e->getMessage() . "\n";
}
