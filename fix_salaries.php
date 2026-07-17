<?php

$records = \App\Models\PlantillaRecord::where('base_salary_amount', '>', 100000)->get();
$count = 0;
foreach($records as $record) {
    $record->base_salary_amount = round($record->base_salary_amount / 12, 2);
    $record->save();
    $count++;
}
echo "Normalized $count records.\n";
