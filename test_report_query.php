<?php
$request = new \Illuminate\Http\Request([
    'report_type' => 'preeval',
    'office' => 'PACCO',
    'position_applied' => 'BPH-MAR-149'
]);

$query = \App\Models\Applicant::query();

if ($request->filled('office')) {
    $rawOffices = \App\Models\Applicant::select('office')->distinct()->pluck('office')->filter()->values();
    $matchingRawOffices = \App\Support\Recruitment\OfficeCanonicalizer::matchingRawValues($rawOffices, [$request->office]);
    $query->whereIn('office', $matchingRawOffices);
}

if ($request->filled('position_applied')) {
    $searchPos = $request->position_applied;
    $query->where(function($q) use ($searchPos) {
        $q->where('position_applied', 'like', "%{$searchPos}%")
          ->orWhere('item_no', 'like', "%{$searchPos}%");
    });
}

if ($request->report_type === 'preeval') {
    $query->whereHas('evaluation', function($q) {
        $q->whereIn('final_rating', ['Qualified', 'Disqualified']);
    });
}

$applicants = $query->with('evaluation')->orderBy('office')->orderBy('position_applied')->orderBy('last_name')->get();
echo json_encode($applicants->pluck('last_name'));
