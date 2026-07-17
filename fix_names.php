<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Remove "null" records
\App\Models\PlantillaRecord::where('last_name', 'null')->orWhereNull('last_name')->delete();

// Fix Mojibake "├æ" -> "Ñ" in PlantillaRecord
$badRecords = \App\Models\PlantillaRecord::where('last_name', 'like', '%├æ%')
    ->orWhere('first_name', 'like', '%├æ%')
    ->orWhere('middle_name', 'like', '%├æ%')
    ->get();

foreach ($badRecords as $record) {
    if ($record->last_name) $record->last_name = str_replace('├æ', 'Ñ', $record->last_name);
    if ($record->first_name) $record->first_name = str_replace('├æ', 'Ñ', $record->first_name);
    if ($record->middle_name) $record->middle_name = str_replace('├æ', 'Ñ', $record->middle_name);
    $record->save();
}

// Fix Mojibake in Applicant
$badApplicants = \App\Models\Applicant::where('last_name', 'like', '%├æ%')
    ->orWhere('first_name', 'like', '%├æ%')
    ->orWhere('middle_name', 'like', '%├æ%')
    ->get();

foreach ($badApplicants as $app) {
    if ($app->last_name) $app->last_name = str_replace('├æ', 'Ñ', $app->last_name);
    if ($app->first_name) $app->first_name = str_replace('├æ', 'Ñ', $app->first_name);
    if ($app->middle_name) $app->middle_name = str_replace('├æ', 'Ñ', $app->middle_name);
    $app->save();
}

echo "Fixed " . $badRecords->count() . " plantilla records and " . $badApplicants->count() . " applicant records.\n";
