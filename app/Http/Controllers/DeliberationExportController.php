<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\HrmpsbPanelMember;
use App\Models\InterviewEvaluation;
use App\Models\TwgScore;
use App\Models\TwgScoreSubmission;
use App\Support\BlindScoringId;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DeliberationExportController extends Controller
{
    public function layoutA(Request $request, Applicant $applicant)
    {
        // No ->load([...]) here: Applicant has no experience/education/eligibility/
        // training relationships (they're flat fields on the model itself, per
        // Module 5.3's profile partial) — calling load() with those names throws
        // BadMethodCallException.
        $pdf = Pdf::loadView('recruitment.exports.layout-a', compact('applicant'));
        $pdf->setPaper('A4', 'portrait');
        
        return $pdf->stream('Layout_A_' . $applicant->last_name . '.pdf');
    }

    public function layoutC(Request $request, Applicant $applicant)
    {
        $twgSubmission = TwgScoreSubmission::where('applicant_id', $applicant->id)->first();
        $twgScores = TwgScore::with('criterion')->where('applicant_id', $applicant->id)->get();
        $hrmpsbEvaluations = InterviewEvaluation::with('panelMember', 'rater')->where('applicant_id', $applicant->id)->get();

        $pdf = Pdf::loadView('recruitment.exports.layout-c', compact('applicant', 'twgSubmission', 'twgScores', 'hrmpsbEvaluations'));
        $pdf->setPaper('legal', 'portrait');

        return $pdf->stream('Layout_C_Scoring_Sheet_' . $applicant->last_name . '.pdf');
    }

    public function layoutD(Request $request, $position)
    {
        // Decode position if encoded
        $position = urldecode($position);
        
        $applicants = Applicant::where('position_applied', $position)->orderBy('last_name')->get();
        $twgSubmissions = TwgScoreSubmission::whereIn('applicant_id', $applicants->pluck('id'))->get()->keyBy('applicant_id');
        $hrmpsbEvaluations = InterviewEvaluation::whereIn('applicant_id', $applicants->pluck('id'))->get()->groupBy('applicant_id');

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="Layout_D_Comparative_' . \Str::slug($position) . '.csv"',
        ];

        $response = new StreamedResponse(function () use ($applicants, $twgSubmissions, $hrmpsbEvaluations, $position) {
            $handle = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($handle, ['Comparative Assessment for: ' . $position]);
            fputcsv($handle, []);
            fputcsv($handle, [
                'Applicant Name',
                'Masked ID',
                'Total Auto Score',
                'Total Assessor Score',
                'Overall Percentage',
                'Adjectival Classification',
                'Recommendation',
                'HRMPSB Interview Average'
            ]);

            foreach ($applicants as $app) {
                $twg = $twgSubmissions->get($app->id);
                $evals = $hrmpsbEvaluations->get($app->id);
                
                $interviewAvg = 0;
                if ($evals && $evals->count() > 0) {
                    $interviewAvg = $evals->avg('total_score');
                }

                fputcsv($handle, [
                    $app->last_name . ', ' . $app->first_name,
                    BlindScoringId::forApplicant($app),
                    $twg ? $twg->total_auto : '0.00',
                    $twg ? $twg->total_assessor : '0.00',
                    $twg ? $twg->percentage : '0.00',
                    $twg ? $twg->adjectival_classification : 'N/A',
                    $twg ? $twg->recommendation : 'Pending',
                    number_format($interviewAvg, 2)
                ]);
            }

            fclose($handle);
        }, 200, $headers);

        return $response;
    }

    public function exportCer(Request $request, Applicant $applicant)
    {
        $twgSubmission = TwgScoreSubmission::where('applicant_id', $applicant->id)->first();
        $twgScores = TwgScore::with('criterion')->where('applicant_id', $applicant->id)->get();
        $hrmpsbEvaluations = InterviewEvaluation::with('panelMember', 'rater')->where('applicant_id', $applicant->id)->get();

        // Infer position level: SG 1-9 is Level 1, SG 10+ is Level 2 (unless PlantillaRecord level is available)
        // Bug fix: PlantillaRecord has no position() relationship — salary_grade
        // and level are direct columns on the record itself.
        $level = 1;
        $sg = 1;
        if ($applicant->item_no) {
            // PlantillaRecord's item-number column is item_no_new (renamed from
            // "item" — see 2026_06_23_105416_normalize_plantilla_records_schema.php),
            // not item_no. Querying item_no throws a SQL error on real MySQL.
            $plantilla = \App\Models\PlantillaRecord::where('item_no_new', $applicant->item_no)->first();
            if ($plantilla) {
                $sg = (int) $plantilla->salary_grade;
                $level = $plantilla->level ?: ($sg >= 10 ? 2 : 1);
            }
        }

        if ($level == 1) {
            $view = 'recruitment.exports.cer-level-1';
        } else {
            $view = 'recruitment.exports.cer-level-2';
        }

        $pdf = Pdf::loadView($view, compact('applicant', 'twgSubmission', 'twgScores', 'hrmpsbEvaluations', 'level', 'sg'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('CER_Level_' . $level . '_' . $applicant->last_name . '.pdf');
    }
}
