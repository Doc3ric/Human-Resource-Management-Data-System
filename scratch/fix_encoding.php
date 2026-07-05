<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require_once dirname(__DIR__).'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = ['job_orders', 'casual_employees', 'plantilla_records'];

$replacementsMap = [
    urldecode('%C3%B1') => 'ñ',
    urldecode('%C3%91') => 'Ñ',
    'Ã±' => 'ñ',
    'Ã‘' => 'Ñ',
    'Ã¡' => 'á',
    'Ã©' => 'é',
    'Ã­' => 'í',
    'Ã³' => 'ó',
    'Ãº' => 'ú',
    'Ã\u0081' => 'Á',
    'Ã\u0089' => 'É',
    'Ã\u008D' => 'Í',
    'Ã\u0093' => 'Ó',
    'Ã\u009A' => 'Ú',
    'â\u0080\u0093' => '-', // en dash
    'â\u0080\u0094' => '--', // em dash
    'â\u0080\u0098' => "'",
    'â\u0080\u0099' => "'",
    'â\u0080\u009C' => '"',
    'â\u0080\u009D' => '"',
    'Ã¢' => 'â',
];

$replacementsMap[utf8_encode('ñ')] = 'ñ';
$replacementsMap[utf8_encode('Ñ')] = 'Ñ';

foreach ($tables as $table) {
    if (!Schema::hasTable($table)) continue;
    
    $columns = Schema::getColumnListing($table);
    $textColumns = [];
    foreach ($columns as $col) {
        $type = Schema::getColumnType($table, $col);
        if (in_array($type, ['string', 'text', 'varchar'])) {
            $textColumns[] = $col;
        }
    }
    
    if (empty($textColumns)) continue;

    echo "Processing table: $table\n";
    $records = DB::table($table)->get();
    $updatedCount = 0;
    
    foreach ($records as $record) {
        $updates = [];
        foreach ($textColumns as $col) {
            $val = $record->$col;
            if (empty($val)) continue;
            
            $newVal = $val;
            foreach ($replacementsMap as $search => $replace) {
                if (strpos($newVal, $search) !== false) {
                    $newVal = str_replace($search, $replace, $newVal);
                }
            }
            if (strpos($newVal, 'Ã±') !== false) $newVal = str_replace('Ã±', 'ñ', $newVal);
            if (strpos($newVal, 'Ã‘') !== false) $newVal = str_replace('Ã‘', 'Ñ', $newVal);
            
            if ($newVal !== $val) {
                $updates[$col] = $newVal;
            }
        }
        
        if (!empty($updates)) {
            DB::table($table)->where('id', $record->id)->update($updates);
            $updatedCount++;
        }
    }
    echo "  Updated $updatedCount records.\n";
}
