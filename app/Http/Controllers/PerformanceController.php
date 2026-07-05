<?php

namespace App\Http\Controllers;

use App\Models\PlantillaRecord;
use App\Models\IpcrRating;
use Illuminate\Http\Request;

class PerformanceController extends Controller
{
    /**
     * Display the performance page.
     */
    public function index(Request $request)
    {
        // Get unique offices for the dropdown, excluding null/empty
        $offices = PlantillaRecord::whereNotNull('office_department')
            ->where('office_department', '!=', '')
            ->distinct()
            ->orderBy('office_department')
            ->pluck('office_department');

        $currentYear = date('Y');

        $targetPenalty = \App\Models\Setting::getVal('target_penalty', '0.25');
        $ratingPenalty = \App\Models\Setting::getVal('rating_penalty', '0.50');

        $threshPoor = \App\Models\Setting::getVal('thresh_poor', '2');
        $threshUnsat = \App\Models\Setting::getVal('thresh_unsat', '3');
        $threshSat = \App\Models\Setting::getVal('thresh_sat', '4');
        $threshVsat = \App\Models\Setting::getVal('thresh_vsat', '5');

        $jjTarget = \App\Models\Setting::getVal('jan_jun_target_deadline', '12-15');
        $jjRating = \App\Models\Setting::getVal('jan_jun_rating_deadline', '07-30');
        $jdTarget = \App\Models\Setting::getVal('jul_dec_target_deadline', '06-15');
        $jdRating = \App\Models\Setting::getVal('jul_dec_rating_deadline', '01-30');

        // IPCR summary stats for the current year
        $ipcrStats = [
            'total_plantilla' => PlantillaRecord::whereNull('nature_of_separation')->whereNull('deleted_at')
                ->where(fn($q) => $q->where('is_vacant', false)->orWhereNull('is_vacant'))->count(),
            'rated'       => IpcrRating::where('year', $currentYear)->distinct('plantilla_record_id')->count('plantilla_record_id'),
            'outstanding' => IpcrRating::where('year', $currentYear)->where('final_rating', '>=', 4.90)->count(),
            'very_sat'    => IpcrRating::where('year', $currentYear)->whereBetween('final_rating', [3.80, 4.8999])->count(),
            'satisfactory'=> IpcrRating::where('year', $currentYear)->whereBetween('final_rating', [2.50, 3.7999])->count(),
            'unsatisfactory'=>IpcrRating::where('year', $currentYear)->where('final_rating', '<', 2.50)->count(),
        ];
        $ipcrStats['unrated'] = max(0, $ipcrStats['total_plantilla'] - $ipcrStats['rated']);

        return view('performance.index', compact(
            'offices', 'currentYear', 'ipcrStats',
            'targetPenalty', 'ratingPenalty',
            'threshPoor', 'threshUnsat', 'threshSat', 'threshVsat',
            'jjTarget', 'jjRating', 'jdTarget', 'jdRating'
        ));
    }

    public function saveSettings(Request $request)
    {
        $request->validate([
            'target_penalty' => 'required|numeric|min:0',
            'rating_penalty' => 'required|numeric|min:0',
            'thresh_poor' => 'required|numeric|min:0',
            'thresh_unsat' => 'required|numeric|min:0',
            'thresh_sat' => 'required|numeric',
            'thresh_vsat' => 'required|numeric',
            'jan_jun_target_deadline' => 'required|string',
            'jan_jun_rating_deadline' => 'required|string',
            'jul_dec_target_deadline' => 'required|string',
            'jul_dec_rating_deadline' => 'required|string',
        ]);

        \App\Models\Setting::setVal('target_penalty', $request->target_penalty);
        \App\Models\Setting::setVal('rating_penalty', $request->rating_penalty);
        \App\Models\Setting::setVal('thresh_poor', $request->thresh_poor);
        \App\Models\Setting::setVal('thresh_unsat', $request->thresh_unsat);
        \App\Models\Setting::setVal('thresh_sat', $request->thresh_sat);
        \App\Models\Setting::setVal('thresh_vsat', $request->thresh_vsat);
        
        \App\Models\Setting::setVal('jan_jun_target_deadline', $request->jan_jun_target_deadline);
        \App\Models\Setting::setVal('jan_jun_rating_deadline', $request->jan_jun_rating_deadline);
        \App\Models\Setting::setVal('jul_dec_target_deadline', $request->jul_dec_target_deadline);
        \App\Models\Setting::setVal('jul_dec_rating_deadline', $request->jul_dec_rating_deadline);

        return response()->json(['success' => true]);
    }

    /**
     * Fetch employees for the selected office via AJAX.
     */
    public function getEmployees(Request $request)
    {
        $office = $request->query('office');
        $year = $request->query('year', date('Y'));
        $period_type = $request->query('period_type', 'jan-jun');
        $custom_period = $request->query('custom_period', null);
        
        if (!$office) {
            return response()->json([]);
        }

        // Fetch non-vacant employees for the office, eager load ipcrRatings for the specific year and period
        $employees = PlantillaRecord::where('office_department', $office)
            ->where('is_vacant', false)
            ->with(['ipcrRatings' => function($q) use ($year, $period_type, $custom_period) {
                $q->where('year', $year)->where('period_type', $period_type);
                if ($period_type === 'custom' && $custom_period) {
                    $q->where('custom_period', $custom_period);
                }
            }])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->orderBy('middle_name')
            ->orderBy('name_extension')
            ->orderBy('position_title')
            ->get([
                'id',
                'last_name',
                'first_name',
                'middle_name',
                'name_extension',
                'position_title',
                'employment_status',
                'office_department'
            ]);

        // Map data for datatables/vue/js
        $data = $employees->map(function ($emp) {
            return [
                'id' => $emp->id,
                'last_name' => $emp->last_name,
                'first_name' => $emp->first_name,
                'middle_name' => $emp->middle_name,
                'name_extension' => $emp->name_extension,
                'position_title' => $emp->position_title,
                'employment_status' => $emp->employment_status,
                'ratings' => $emp->ipcrRatings 
            ];
        });

        return response()->json($data);
    }

    /**
     * Save an individual rating / target submission.
     */
    public function saveRating(Request $request)
    {
        $request->validate([
            'plantilla_record_id' => 'required|exists:plantilla_records,id',
            'period_type' => 'required|string|in:jan-jun,jul-dec,custom',
            'custom_period' => 'nullable|string',
            'year' => 'required|integer',
            'target_submitted' => 'required|boolean',
            'target_submission_date' => 'nullable|date',
            'rating' => 'nullable|numeric|min:0|max:5',
            'rating_submission_date' => 'nullable|date',
        ]);

        $ipcr = IpcrRating::updateOrCreate(
            [
                'plantilla_record_id' => $request->plantilla_record_id,
                'year' => $request->year,
                'period_type' => $request->period_type,
                'custom_period' => $request->period_type === 'custom' ? $request->custom_period : null,
            ],
            [
                'target_submitted' => $request->target_submitted,
                'target_submission_date' => $request->target_submission_date,
                'rating' => $request->rating,
                'rating_submission_date' => $request->rating_submission_date,
            ]
        );

        return response()->json(['success' => true, 'data' => $ipcr]);
    }

    /**
     * Batch save targets and ratings.
     */
    public function batchSave(Request $request)
    {
        $request->validate([
            'ratings' => 'required|array',
            'ratings.*.employee_id' => 'required|exists:plantilla_records,id',
            'ratings.*.target_submitted' => 'required|boolean',
            'ratings.*.target_submission_date' => 'nullable|date',
            'ratings.*.rating' => 'nullable|numeric|min:0|max:5',
            'ratings.*.rating_submission_date' => 'nullable|date',
            'period_type' => 'required|string|in:jan-jun,jul-dec,custom',
            'custom_period' => 'nullable|string',
            'year' => 'required|integer',
        ]);

        $year = $request->year;
        $period_type = $request->period_type;
        $custom_period = $period_type === 'custom' ? $request->custom_period : null;

        foreach ($request->ratings as $item) {
            IpcrRating::updateOrCreate(
                [
                    'plantilla_record_id' => $item['employee_id'],
                    'year' => $year,
                    'period_type' => $period_type,
                    'custom_period' => $custom_period,
                ],
                [
                    'target_submitted' => $item['target_submitted'],
                    'target_submission_date' => $item['target_submission_date'] ?? null,
                    'rating' => $item['rating'] ?? null,
                    'rating_submission_date' => $item['rating_submission_date'] ?? null,
                ]
            );
        }

        return response()->json(['success' => true]);
    }


    /**
     * Export to Excel based on criteria and selected fields.
     */
    public function export(Request $request)
    {
        $request->validate([
            'office' => 'required|string',
            'year' => 'required|integer',
            'period_type' => 'required|string',
            'custom_period' => 'nullable|string',
            'fields' => 'required|array',
            'adjectival_rating' => 'nullable|string'
        ]);

        $office = $request->office;
        $year = $request->year;
        $period_type = $request->period_type;
        $custom_period = $request->custom_period;
        $fields = $request->fields;
        $adjFilter = strtolower($request->adjectival_rating ?? '');

        // Fetch non-vacant employees for the office, eager load ipcrRatings
        $employees = PlantillaRecord::where('office_department', $office)
            ->where('is_vacant', false)
            ->with(['ipcrRatings' => function($q) use ($year, $period_type, $custom_period) {
                $q->where('year', $year)->where('period_type', $period_type);
                if ($period_type === 'custom' && $custom_period) {
                    $q->where('custom_period', $custom_period);
                }
            }])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->orderBy('middle_name')
            ->orderBy('name_extension')
            ->orderBy('position_title')
            ->get();

        // Apply adjectival rating filter manually if selected
        if ($adjFilter) {
            $employees = $employees->filter(function ($emp) use ($adjFilter) {
                $ipcr = $emp->ipcrRatings->first();
                if (!$ipcr) return false;
                return strtolower($ipcr->adjectival_rating) === $adjFilter;
            });
        }

        $export = new \App\Exports\PerformanceExport($employees, $fields);
        $fileName = 'Performance_Report_' . date('Ymd_His') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download($export, $fileName);
    }

    public function downloadTemplate(Request $request)
    {
        $request->validate([
            'office' => 'required|string',
            'year' => 'required|integer',
            'period_type' => 'required|string',
            'custom_period' => 'nullable|string',
        ]);

        $export = new \App\Exports\PerformanceTemplateExport(
            $request->office,
            $request->year,
            $request->period_type,
            $request->custom_period
        );

        $fileName = 'Ratings_Template_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $request->office) . '_' . $request->year . '_' . $request->period_type . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download($export, $fileName);
    }

    public function importRatings(Request $request)
    {
        $request->validate([
            'year' => 'required|integer',
            'period_type' => 'required|string',
            'custom_period' => 'nullable|string',
            'import_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(
                new \App\Imports\PerformanceRatingsImport(
                    $request->year,
                    $request->period_type,
                    $request->custom_period
                ),
                $request->file('import_file')
            );

            return response()->json(['success' => true, 'message' => 'Ratings imported successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error importing file: ' . $e->getMessage()], 400);
        }
    }
}
