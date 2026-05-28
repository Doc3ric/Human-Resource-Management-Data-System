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

        // ── Plantilla Records ────────────────────────────────────────────────
        if (in_array($table, ['all', 'plantilla'])) {
            $records = PlantillaRecord::whereNull('employee_code')
                ->where('is_vacant', false)
                ->whereNotNull('last_name')
                ->whereNotNull('date_of_birth')
                ->get(['id', 'last_name', 'date_of_birth', 'first_name']);

            foreach ($records as $r) {
                $code = PlantillaRecord::generateEmployeeCode($r->last_name, $r->date_of_birth, $r->id);
                if (!$code) { $skipped['plantilla']++; continue; }

                // Track if a suffix was needed (code differs from base)
                $base = \Carbon\Carbon::parse($r->date_of_birth)->format('dmY') . strtoupper(substr(trim($r->last_name), 0, 1));
                if ($code !== $base) {
                    $conflicts[] = "Plantilla #{$r->id} ({$r->last_name}): duplicate resolved → {$code}";
                }

                $r->updateQuietly(['employee_code' => $code]);
                $totals['plantilla']++;
            }
        }

        // ── Casual Employees ─────────────────────────────────────────────────
        if (in_array($table, ['all', 'casual'])) {
            $records = CasualEmployee::whereNull('employee_code')
                ->where('is_vacant', false)
                ->whereNotNull('last_name')
                ->whereNotNull('birthdate')
                ->get(['id', 'last_name', 'birthdate', 'first_name']);

            foreach ($records as $r) {
                $code = CasualEmployee::generateEmployeeCode($r->last_name, $r->birthdate, $r->id);
                if (!$code) { $skipped['casual']++; continue; }

                $base = \Carbon\Carbon::parse($r->birthdate)->format('dmY') . strtoupper(substr(trim($r->last_name), 0, 1));
                if ($code !== $base) {
                    $conflicts[] = "Casual #{$r->id} ({$r->last_name}): duplicate resolved → {$code}";
                }

                $r->updateQuietly(['employee_code' => $code]);
                $totals['casual']++;
            }
        }

        // ── Job Orders ───────────────────────────────────────────────────────
        if (in_array($table, ['all', 'jo'])) {
            $records = JobOrder::whereNull('employee_code')
                ->whereNotNull('last_name')
                ->whereNotNull('birthdate')
                ->get(['id', 'last_name', 'birthdate', 'first_name']);

            foreach ($records as $r) {
                $code = JobOrder::generateEmployeeCode($r->last_name, $r->birthdate, $r->id);
                if (!$code) { $skipped['jo']++; continue; }

                $base = \Carbon\Carbon::parse($r->birthdate)->format('dmY') . strtoupper(substr(trim($r->last_name), 0, 1));
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
