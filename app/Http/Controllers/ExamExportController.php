<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\ExamSchedule;
use App\Support\BlindScoringId;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ExamExportController extends Controller
{
    private function getApplicantsForSchedule($classification, $date, $room, $time)
    {
        $query = ExamSchedule::with('applicant')->where('classification', $classification);

        if ($date && $date !== 'none') {
            $query->whereDate('exam_date', $date);
        } else {
            $query->whereNull('exam_date');
        }

        if ($room && $room !== 'none') {
            $query->where('room', urldecode($room));
        } else {
            $query->whereNull('room');
        }

        if ($time && $time !== 'none') {
            $query->where('exam_time', urldecode($time));
        } else {
            $query->whereNull('exam_time');
        }

        $schedules = $query->get();

        // Pluck the applicants, retaining the table number
        $applicants = $schedules->map(function ($schedule) {
            $app = $schedule->applicant;
            if ($app) {
                $app->table_number = $schedule->table_number;
                $app->exam_date_time = ($schedule->exam_date ? $schedule->exam_date->format('M d, Y') : 'TBA') . ' ' . ($schedule->exam_time ?: 'TBA');
            }
            return $app;
        })->filter();

        // Module 8.1 Sorting: Group by Office -> Position Title -> Status -> sort alphabetically by Last Name
        // Assuming 'office' and 'position_applied' are available on Applicant
        return $applicants->sortBy([
            ['office', 'asc'],
            ['position_applied', 'asc'],
            ['pgb_status', 'asc'],
            ['last_name', 'asc']
        ])->values();
    }

    public function layoutA(Request $request, $classification, $date, $room, $time)
    {
        $applicants = $this->getApplicantsForSchedule($classification, $date, $room, $time);

        $pdf = Pdf::loadView('recruitment.exports.exam-layout-a', compact('applicants', 'classification', 'date', 'room', 'time'));
        $pdf->setPaper('legal', 'landscape');
        
        return $pdf->stream('Layout_A_Examiners_Copy_' . $classification . '_' . $date . '.pdf');
    }

    public function layoutB(Request $request, $classification, $date, $room, $time)
    {
        $applicants = $this->getApplicantsForSchedule($classification, $date, $room, $time);

        $pdf = Pdf::loadView('recruitment.exports.exam-layout-b', compact('applicants', 'classification', 'date', 'room', 'time'));
        $pdf->setPaper('legal', 'landscape');
        
        return $pdf->stream('Layout_B_Public_Copy_' . $classification . '_' . $date . '.pdf');
    }
}
