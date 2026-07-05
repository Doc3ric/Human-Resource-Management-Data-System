<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Applicant;
use App\Support\ExamRoutingService;
use Illuminate\Http\Request;

/** Module 5.4 — examination routing engine (PGB JO / External / Exempted classification + clustering). */
class ExamRoutingController extends Controller
{
    public function __construct(private readonly ExamRoutingService $engine)
    {
    }

    public function index(Request $request)
    {
        $windowMonths = (int) $request->input('window_months', ExamRoutingService::DEFAULT_WINDOW_MONTHS);
        $tableSize = (int) $request->input('table_size', ExamRoutingService::DEFAULT_TABLE_SIZE);

        $grouped = $this->engine->windowedApplicants($windowMonths);

        $pgbJo = $this->engine->cluster($grouped->get('pgb_jo', collect()), $tableSize);
        $external = $this->engine->cluster($grouped->get('external', collect()), $tableSize);
        $exempt = $grouped->get('exempt', collect());

        return view('recruitment.exam-routing.index', compact('pgbJo', 'external', 'exempt', 'windowMonths', 'tableSize'));
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'classification' => 'required|in:pgb_jo,external',
            'window_months' => 'required|integer|min:1',
            'table_size' => 'required|integer|min:1',
            'exam_date' => 'nullable|date',
            'exam_time' => 'nullable',
            'room' => 'nullable|string',
        ]);

        $grouped = $this->engine->windowedApplicants($validated['window_months']);
        $tables = $this->engine->cluster($grouped->get($validated['classification'], collect()), $validated['table_size']);

        $this->engine->generate(
            $tables,
            $validated['classification'],
            $validated['exam_date'] ?? null,
            $validated['exam_time'] ?? null,
            $validated['room'] ?? null,
            $request->user()
        );

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Generated Exam Schedule',
            'description' => "Generated exam table assignments for {$validated['classification']} ({$tables->flatten(1)->count()} applicants in {$tables->count()} table(s)).",
        ]);

        return back()->with('success', 'Exam schedule generated for ' . $tables->flatten(1)->count() . ' applicant(s).');
    }

    public function toggleExempt(Request $request, Applicant $applicant)
    {
        $applicant->update(['is_exam_exempt' => !$applicant->is_exam_exempt]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'action' => 'Toggled Exam Exemption',
            'description' => "Set is_exam_exempt={$applicant->is_exam_exempt} for {$applicant->last_name}, {$applicant->first_name}.",
        ]);

        return back()->with('success', 'Exemption status updated.');
    }
}
