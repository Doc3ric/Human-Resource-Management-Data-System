<?php
$rawOffices = \App\Models\Applicant::select('office')->distinct()->pluck('office')->filter()->values();
$matchingRawOffices = \App\Support\Recruitment\OfficeCanonicalizer::matchingRawValues($rawOffices, ['PACCO']);
$positionRows = \App\Models\Applicant::whereIn('office', $matchingRawOffices)->whereNotNull('position_applied')->get(['position_applied', 'item_no']);
$conditions = \App\Support\Recruitment\VacancyIdentifier::matchingConditions($positionRows, ['BPH-MAR-149']);
$query = \App\Models\Applicant::whereIn('office', $matchingRawOffices);
\App\Support\Recruitment\VacancyIdentifier::applyMatch($query, $conditions);
$applicants = $query
    ->orderByDesc('id')
    ->get(['id', 'last_name', 'first_name', 'item_no', 'position_applied', 'office'])
    ->unique(function ($app) {
        return strtolower(trim($app->first_name) . '|' . trim($app->last_name));
    })
    ->sortBy('last_name')
    ->values()
    ->toArray();

dump($applicants);
