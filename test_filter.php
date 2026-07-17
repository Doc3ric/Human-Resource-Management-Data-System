<?php
$query = \App\Models\Applicant::query();
$positionRows = \App\Models\Applicant::whereNotNull('position_applied')->get(['position_applied', 'item_no']);
$conditions = \App\Support\Recruitment\VacancyIdentifier::matchingConditions($positionRows, ['BPH-MAR-149']);
\App\Support\Recruitment\VacancyIdentifier::applyMatch($query, $conditions);
echo json_encode($query->get(['id', 'last_name', 'item_no', 'position_applied']));
