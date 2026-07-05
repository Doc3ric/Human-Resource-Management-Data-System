<?php

namespace App\Http\Controllers;

use App\Models\PlantillaRecord;
use App\Models\CasualEmployee;
use App\Models\JobOrder;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeCodeController extends Controller
{
    /**
     * Generate employee codes for ALL existing records that don't have one yet.
     * Runs across all three tables: plantilla_records, casual_employees, job_orders.
     */
    public function generateAll(Request $request)
    {
        $table   = $request->input('table', 'all'); // 'all' | 'plantilla' | 'casual' | 'jo'
        $totals  = ['plantilla' => 0, 'casual' => 0, 'jo' => 0];
        $skipped = ['plantilla' => 0, 'casual' => 0, 'jo' => 0];
        $conflicts = [];

        // ── Plantilla Records (all filled records, regenerate existing codes too) ──
        if (in_array($table, ['all', 'plantilla'])) {
            $records = PlantillaRecord::where('is_vacant', false)
                ->whereNotNull('first_name')
                ->whereNotNull('date_of_birth')
                ->get(['id', 'first_name', 'middle_name', 'last_name', 'date_of_birth', 'employee_code']);

            // Clear all codes first so uniqueness check works correctly during re-gen
            PlantillaRecord::where('is_vacant', false)->update(['employee_code' => null]);

            foreach ($records as $r) {
                $code = PlantillaRecord::generateEmployeeCode($r->first_name, $r->date_of_birth, $r->id, $r->middle_name, $r->last_name);
                if (!$code) { $skipped['plantilla']++; continue; }

                $base = \Carbon\Carbon::parse($r->date_of_birth)->format('dmY') . strtoupper(substr(trim($r->first_name), 0, 1));
                if ($code !== $base) {
                    $conflicts[] = "Plantilla #{$r->id} ({$r->last_name}): duplicate resolved → {$code}";
                }

                $r->updateQuietly(['employee_code' => $code]);
                $totals['plantilla']++;
            }
        }

        // ── Casual Employees (all filled, regenerate existing codes too) ──────
        if (in_array($table, ['all', 'casual'])) {
            $records = CasualEmployee::where('is_vacant', false)
                ->whereNotNull('first_name')
                ->whereNotNull('date_of_birth')
                ->get(['id', 'first_name', 'middle_name', 'last_name', 'date_of_birth', 'employee_code']);

            CasualEmployee::where('is_vacant', false)->update(['employee_code' => null]);

            foreach ($records as $r) {
                $code = CasualEmployee::generateEmployeeCode($r->first_name, $r->date_of_birth, $r->id, $r->middle_name, $r->last_name);
                if (!$code) { $skipped['casual']++; continue; }

                $base = \Carbon\Carbon::parse($r->date_of_birth)->format('dmY') . strtoupper(substr(trim($r->first_name), 0, 1));
                if ($code !== $base) {
                    $conflicts[] = "Casual #{$r->id} ({$r->last_name}): duplicate resolved → {$code}";
                }

                $r->updateQuietly(['employee_code' => $code]);
                $totals['casual']++;
            }
        }

        // ── Job Orders (all, regenerate existing codes too) ──────────────────
        if (in_array($table, ['all', 'jo'])) {
            $records = JobOrder::whereNotNull('first_name')
                ->whereNotNull('date_of_birth')
                ->get(['id', 'first_name', 'middle_name', 'last_name', 'date_of_birth', 'employee_code']);

            JobOrder::query()->update(['employee_code' => null]);

            foreach ($records as $r) {
                $code = JobOrder::generateEmployeeCode($r->first_name, $r->date_of_birth, $r->id, $r->middle_name, $r->last_name);
                if (!$code) { $skipped['jo']++; continue; }

                $base = \Carbon\Carbon::parse($r->date_of_birth)->format('dmY') . strtoupper(substr(trim($r->first_name), 0, 1));
                if ($code !== $base) {
                    $conflicts[] = "JO #{$r->id} ({$r->last_name}): duplicate resolved → {$code}";
                }

                $r->updateQuietly(['employee_code' => $code]);
                $totals['jo']++;
            }
        }

        // Log the action
        if (Auth::check()) {
            $totalGenerated = array_sum($totals);
            ActivityLog::create([
                'user_id'     => Auth::id(),
                'action'      => 'Generated Employee Codes',
                'description' => "Bulk generated {$totalGenerated} employee codes — "
                    . "Plantilla: {$totals['plantilla']}, Casual: {$totals['casual']}, JO: {$totals['jo']}."
                    . (count($conflicts) ? ' Conflicts resolved: ' . count($conflicts) : ''),
            ]);
        }

        $totalGenerated = array_sum($totals);
        $message = "✅ Successfully generated {$totalGenerated} employee code(s). "
            . "Plantilla: {$totals['plantilla']}, Casual: {$totals['casual']}, JO: {$totals['jo']}.";

        if (!empty($conflicts)) {
            $message .= " ⚠️ " . count($conflicts) . " duplicate(s) were resolved with unique suffixes.";
        }

        return back()->with('success', $message);
    }
}
