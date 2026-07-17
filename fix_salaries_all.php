<?php
$count = 0;
\App\Models\PlantillaRecord::withoutGlobalScopes()->chunk(500, function ($records) use (&$count) {
    foreach ($records as $record) {
        if ($record->base_salary_amount > 100000 && $record->salary_type === 'Annual') {
            $record->base_salary_amount = round($record->base_salary_amount / 12, 2);
            $record->salary_type = 'Monthly';
            $record->save();
            $count++;
        }
    }
});
echo "Fixed " . $count . " records.\n";
