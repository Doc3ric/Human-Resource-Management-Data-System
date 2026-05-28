<?php

namespace App\Http\Controllers;

use App\Models\SalaryGrade;
use App\Models\SalarySchedule;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalaryGradeController extends Controller
{
    /**
     * Display the salary grade matrix for the active schedule (or legacy rows).
     */
    public function index()
    {
        $activeSchedule = SalarySchedule::getActive();

        if ($activeSchedule) {
            $records = SalaryGrade::where('salary_schedule_id', $activeSchedule->id)
                                  ->orderBy('grade')->orderBy('step')->get();
        } else {
            $records = SalaryGrade::whereNull('salary_schedule_id')
                                  ->orderBy('grade')->orderBy('step')->get();
        }

        $matrix = [];
        foreach ($records as $record) {
            $matrix[$record->grade][$record->step] = $record->monthly_salary;
        }

        return view('salary-grades.index', compact('matrix', 'activeSchedule'));
    }

    /**
     * Update the salary grade matrix for the active schedule (or legacy rows).
     */
    public function updateAll(Request $request)
    {
        $this->authorizeAccess();

        $data           = $request->except(['_token', '_method']);
        $activeSchedule = SalarySchedule::getActive();

        DB::beginTransaction();

        try {
            foreach ($data as $key => $value) {
                if (preg_match('/^grade_(\d+)_step_(\d+)$/', $key, $matches)) {
                    $grade  = (int) $matches[1];
                    $step   = (int) $matches[2];
                    $salary = (float) $value;

                    SalaryGrade::updateOrCreate(
                        [
                            'salary_schedule_id' => $activeSchedule?->id,
                            'grade'              => $grade,
                            'step'               => $step,
                        ],
                        ['monthly_salary' => $salary]
                    );
                }
            }

            $scheduleName = $activeSchedule ? '"' . $activeSchedule->name . '"' : 'legacy baseline';

            if (Auth::check()) {
                ActivityLog::create([
                    'user_id'     => Auth::id(),
                    'action'      => 'Modified Data',
                    'description' => 'Updated Salary Grade Table for ' . $scheduleName . ' (all steps & grades)',
                ]);
            }

            DB::commit();
            return redirect()->route('salary-grades.index')
                             ->with('success', 'Salary Grade table updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update the Salary Grade table. ' . $e->getMessage());
        }
    }

    private function authorizeAccess()
    {
        if (!Auth::check()) {
            abort(403);
        }
    }
}
