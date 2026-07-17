<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\PlantillaRecord;
use App\Models\SalaryGrade;
use App\Models\SalarySchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalaryScheduleController extends Controller
{
    // ── Index ─────────────────────────────────────────────────────────────────

    public function index()
    {
        $schedules      = SalarySchedule::orderByDesc('effective_date')->get();
        $activeSchedule = SalarySchedule::getActive();

        // Budget Requirement: sum of all annual salaries for non-vacant, non-abolished plantilla
        $budgetTotal = PlantillaRecord::where('is_vacant', false)
                            ->where('abolished', false)
                            ->sum('base_salary_amount');

        return view('salary-schedules.index', compact('schedules', 'activeSchedule', 'budgetTotal'));
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function create()
    {
        $activeSchedule = SalarySchedule::getActive();

        // Pre-load the active schedule's matrix so the form has default values
        $matrix = [];
        if ($activeSchedule) {
            $rows = SalaryGrade::where('salary_schedule_id', $activeSchedule->id)
                               ->orderBy('grade')->orderBy('step')->get();
        } else {
            // Fall back to legacy rows
            $rows = SalaryGrade::whereNull('salary_schedule_id')
                               ->orderBy('grade')->orderBy('step')->get();
        }
        foreach ($rows as $row) {
            $matrix[$row->grade][$row->step] = $row->monthly_salary;
        }

        return view('salary-schedules.create', compact('matrix', 'activeSchedule'));
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'law_name'       => 'nullable|string|max:255',
            'lbc_number'     => 'nullable|string|max:50',
            'effective_date' => 'nullable|date',
            'description'    => 'nullable|string',
            'set_active'     => 'nullable|boolean',
        ]);

        // Collect salary grade inputs (grade_X_step_Y)
        $gradeData = [];
        foreach ($request->except(['_token', 'name', 'law_name', 'effective_date', 'description', 'set_active']) as $key => $value) {
            if (preg_match('/^grade_(\d+)_step_(\d+)$/', $key, $m)) {
                $gradeData[] = [
                    'grade'          => (int) $m[1],
                    'step'           => (int) $m[2],
                    'monthly_salary' => (float) $value,
                ];
            }
        }

        DB::beginTransaction();
        try {
            // If setting active, deactivate all others first
            if ($request->boolean('set_active')) {
                SalarySchedule::query()->update(['is_active' => false]);
            }

            $schedule = SalarySchedule::create([
                'name'           => $request->input('name'),
                'law_name'       => $request->input('law_name'),
                'lbc_number'     => $request->input('lbc_number'),
                'effective_date' => $request->input('effective_date'),
                'description'    => $request->input('description'),
                'is_active'      => $request->boolean('set_active'),
            ]);

            // Bulk-insert salary grade rows for this schedule
            $now  = now();
            $rows = collect($gradeData)->map(fn ($g) => array_merge($g, [
                'salary_schedule_id' => $schedule->id,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]))->all();

            // Insert in chunks to avoid max-placeholder issues
            foreach (array_chunk($rows, 200) as $chunk) {
                SalaryGrade::insert($chunk);
            }

            if (Auth::check()) {
                ActivityLog::create([
                    'user_id'     => Auth::id(),
                    'action'      => 'Created SSL Schedule',
                    'description' => 'Created salary schedule "' . $schedule->name . '"'
                                   . ($schedule->is_active ? ' and set it as active' : ''),
                ]);
            }

            DB::commit();
            return redirect()->route('salary-schedules.index')
                             ->with('success', 'Salary Schedule "' . $schedule->name . '" created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                         ->with('error', 'Failed to create salary schedule: ' . $e->getMessage());
        }
    }

    // ── Set Active ────────────────────────────────────────────────────────────

    public function setActive(SalarySchedule $schedule)
    {
        DB::beginTransaction();
        try {
            $schedule->activate();

            if (Auth::check()) {
                ActivityLog::create([
                    'user_id'     => Auth::id(),
                    'action'      => 'Activated SSL Schedule',
                    'description' => 'Set "' . $schedule->name . '" as the active salary schedule',
                ]);
            }

            DB::commit();
            return back()->with('success', '"' . $schedule->name . '" is now the active salary schedule.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to activate schedule: ' . $e->getMessage());
        }
    }

    // ── Apply to All Plantilla ────────────────────────────────────────────────

    public function applyToPlantilla(SalarySchedule $schedule)
    {
        $sql = "
            UPDATE plantilla_records pr
            INNER JOIN salary_grades sg
                ON pr.salary_grade = sg.grade 
                AND IF(COALESCE(pr.step, '') = '', 1, pr.step) = sg.step
                AND sg.salary_schedule_id = ?
            SET
                pr.base_salary_amount      = sg.monthly_salary,
                pr.authorized_annual_salary  = (sg.monthly_salary * 12),
                pr.updated_at                = NOW()
            WHERE pr.deleted_at IS NULL
        ";

        try {
            $affected = DB::update($sql, [$schedule->id]);

            if (Auth::check()) {
                ActivityLog::create([
                    'user_id'     => Auth::id(),
                    'action'      => 'Applied SSL Schedule',
                    'description' => 'Applied "' . $schedule->name . '" to plantilla — updated ' . $affected . ' record(s)',
                ]);
            }

            return back()->with('success', 'Applied "' . $schedule->name . '" to plantilla. ' . $affected . ' record(s) updated.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to apply schedule: ' . $e->getMessage());
        }
    }

    // ── Edit ──────────────────────────────────────────────────────────────────

    public function edit(SalarySchedule $salarySchedule)
    {
        $matrix = [];
        $rows = SalaryGrade::where('salary_schedule_id', $salarySchedule->id)
                           ->orderBy('grade')->orderBy('step')->get();
        foreach ($rows as $row) {
            $matrix[$row->grade][$row->step] = $row->monthly_salary;
        }

        return view('salary-schedules.edit', compact('salarySchedule', 'matrix'));
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function update(Request $request, SalarySchedule $salarySchedule)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'law_name'       => 'nullable|string|max:255',
            'lbc_number'     => 'nullable|string|max:50',
            'effective_date' => 'nullable|date',
            'description'    => 'nullable|string',
        ]);

        // Collect salary grade inputs (grade_X_step_Y)
        $gradeData = [];
        foreach ($request->except(['_token', '_method', 'name', 'law_name', 'lbc_number', 'effective_date', 'description']) as $key => $value) {
            if (preg_match('/^grade_(\d+)_step_(\d+)$/', $key, $m)) {
                $gradeData[] = [
                    'grade'          => (int) $m[1],
                    'step'           => (int) $m[2],
                    'monthly_salary' => (float) $value,
                ];
            }
        }

        DB::beginTransaction();
        try {
            $salarySchedule->update([
                'name'           => $request->input('name'),
                'law_name'       => $request->input('law_name'),
                'lbc_number'     => $request->input('lbc_number'),
                'effective_date' => $request->input('effective_date'),
                'description'    => $request->input('description'),
            ]);

            // Upsert grade rows to avoid unique constraint violations with soft deletes
            $now  = now();
            $upsertData = collect($gradeData)->map(fn ($g) => [
                'salary_schedule_id' => $salarySchedule->id,
                'grade'              => $g['grade'],
                'step'               => $g['step'],
                'monthly_salary'     => $g['monthly_salary'],
                'created_at'         => $now,
                'updated_at'         => $now,
                'deleted_at'         => null,
            ])->all();

            foreach (array_chunk($upsertData, 100) as $chunk) {
                SalaryGrade::upsert(
                    $chunk,
                    ['salary_schedule_id', 'grade', 'step'], // unique keys
                    ['monthly_salary', 'updated_at', 'deleted_at'] // columns to update
                );
            }

            if (Auth::check()) {
                ActivityLog::create([
                    'user_id'     => Auth::id(),
                    'action'      => 'Updated SSL Schedule',
                    'description' => 'Updated salary schedule "' . $salarySchedule->name . '"',
                ]);
            }

            DB::commit();
            return redirect()->route('salary-schedules.index')
                             ->with('success', 'Salary Schedule "' . $salarySchedule->name . '" updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                         ->with('error', 'Failed to update salary schedule: ' . $e->getMessage());
        }
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(SalarySchedule $schedule)
    {
        if ($schedule->is_active) {
            return back()->with('error', 'Cannot archive the active salary schedule. Please activate another schedule first, then archive this one.');
        }

        $name = $schedule->name;
        
        // Soft delete child records first since DB cascade doesn't apply to soft deletes
        $schedule->salaryGrades()->delete();
        // Soft delete parent record
        $schedule->delete(); 

        if (Auth::check()) {
            ActivityLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'Archived SSL Schedule',
                'description' => 'Archived salary schedule "' . $name . '"',
            ]);
        }

        return back()->with('success', 'Salary schedule "' . $name . '" archived successfully.');
    }
}
