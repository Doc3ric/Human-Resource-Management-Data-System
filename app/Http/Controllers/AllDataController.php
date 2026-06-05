<?php

namespace App\Http\Controllers;

use App\Models\PlantillaRecord;
use App\Models\ActivityLog;
use App\Exports\AllDataExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class AllDataController extends Controller
{
    // ── Helper: shared validation rules ──────────────────────────────────────

    private function validationRules(?int $ignoreId = null): array
    {
        return [
            'employee_code' => 'nullable|string|max:20|unique:plantilla_records,employee_code' . ($ignoreId ? ",{$ignoreId}" : ''),
            'organizational_unit' => 'nullable|string|max:255',
            'item' => 'nullable|string|max:255|unique:plantilla_records,item' . ($ignoreId ? ",{$ignoreId}" : ''),
            'position_title' => 'nullable|string|max:255',
            'salary_grade' => 'nullable|integer|min:1|max:33',
            'authorized_annual_salary' => 'nullable|numeric|min:0',
            'actual_annual_salary' => 'nullable|numeric|min:0',
            'step' => 'nullable|integer|min:1|max:8',
            'area_code' => 'nullable|string|max:10',
            'area_type' => 'nullable|string|max:5',
            'level' => 'nullable|string|max:5',
            'last_name' => 'nullable|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'sex' => 'nullable|in:M,F',
            'religion' => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|date',
            'tin' => 'nullable|string|max:50',
            'date_original_appointment' => 'nullable|date',
            'date_last_promotion' => 'nullable|date',
            'date_last_nolp' => 'nullable|date',
            'employment_status' => 'nullable|string|max:50',
            'civil_service_eligibility' => 'nullable|string|max:255',
            'comment_annotation' => 'nullable|string',
            'is_pwd' => 'boolean',
            'type_of_disability' => 'nullable|string|max:100',
            'indigenous_people' => 'nullable|string|max:50',
            'solo_parent' => 'nullable|string|max:255',
            'abolished' => 'boolean',
            'dissolved' => 'boolean',
            'gsis_bp_number' => 'nullable|string|max:50',
            'position_classification' => 'nullable|string|max:100',
            'umid' => 'nullable|string|max:50',
            'is_vacant' => 'nullable|boolean',
            'is_apprehended' => 'boolean',
            'is_admin_charge' => 'boolean',
            'apprehended_from' => 'nullable|date',
            'admin_charge_from' => 'nullable|date',
            'admin_charge_to' => 'nullable|date',
            'is_health_worker' => 'boolean',
            'admin_charges' => 'nullable|array',
            'process_separation' => 'nullable|boolean',
            'nature_of_separation' => 'nullable|string|max:100',
            'date_separated' => 'nullable|date',
            'admin_charges.*.from' => 'nullable|date',
            'admin_charges.*.to' => 'nullable|date',
            'admin_charges.*.type' => 'nullable|string|max:255',
            'nature_of_separation' => 'nullable|string|max:100',
        ];
    }

    // ── Index: flat paginated table ───────────────────────────────────────────

    public function index(Request $request)
    {
        $query = PlantillaRecord::query()
            ->orderBy('organizational_unit')
            ->orderBy('item');

        // Global search
        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('last_name', 'like', "%{$term}%")
                    ->orWhere('first_name', 'like', "%{$term}%")
                    ->orWhere('middle_name', 'like', "%{$term}%")
                    ->orWhere('item', 'like', "%{$term}%")
                    ->orWhere('position_title', 'like', "%{$term}%")
                    ->orWhere('organizational_unit', 'like', "%{$term}%")
                    ->orWhere('tin', 'like', "%{$term}%");
            });
        }

        // Office filter
        if ($request->filled('office')) {
            $query->where('organizational_unit', $request->input('office'));
        }

        // Status filter
        if ($request->filled('status')) {
            $status = $request->input('status');
            $statusMap = [
                'P' => ['P', 'Permanent'],
                'CT' => ['CT', 'Co-Terminous', 'Coterminous'],
                'E' => ['E', 'Elected'],
                'Casual' => ['Casual', 'Cas'],
                'JO' => ['JO', 'Job Order', 'J.O.'],
            ];
            if (isset($statusMap[$status])) {
                $query->whereIn('employment_status', $statusMap[$status]);
            }
        }

        // Sex filter
        if ($request->filled('sex')) {
            $query->where('sex', $request->input('sex'));
        }

        // Vacant / Filled filter
        if ($request->filled('vacant')) {
            $vacant = $request->input('vacant');
            if ($vacant === 'vacant') {
                $query->where('is_vacant', true);
            } elseif ($vacant === 'vacant_funded') {
                $query->where('is_vacant', true)->where('abolished', false)->where('dissolved', false);
            } elseif ($vacant === 'vacant_unfunded') {
                $query->where(function($q) {
                    $q->where(function($q2) {
                        $q2->where('is_vacant', true)
                           ->where(function($q3) {
                               $q3->where('abolished', true)->orWhere('dissolved', true);
                           });
                    })->orWhere(function($q2) {
                        $q2->where(function($q3) {
                            $q3->whereNull('authorized_annual_salary')->orWhere('authorized_annual_salary', 0);
                        })->where(function($q3) {
                            $q3->whereNull('actual_annual_salary')->orWhere('actual_annual_salary', 0);
                        });
                    });
                });
            } elseif ($vacant === 'filled') {
                $query->where('is_vacant', false);
            }
        }

        // PWD filter
        if ($request->boolean('pwd')) {
            $query->where('is_pwd', true);
        }

        // IP filter
        if ($request->boolean('ip')) {
            $query->whereNotNull('indigenous_people')->where('indigenous_people', '!=', '');
        }

        // Solo Parent filter
        if ($request->boolean('solo_parent')) {
            $query->whereNotNull('solo_parent')->where('solo_parent', '!=', '')->where('solo_parent', '!=', '-');
        }

        // Abolished filter
        if ($request->boolean('abolished')) {
            $query->where('abolished', true);
        }

        // Position Title filter
        if ($request->filled('position')) {
            $query->where('position_title', $request->input('position'));
        }

        $perPage = $request->input('per_page', 50);
        $records = $query->paginate($perPage)->withQueryString();
        $offices = PlantillaRecord::distinct()->orderBy('organizational_unit')
            ->pluck('organizational_unit')->filter()->values();
        $positions = PlantillaRecord::distinct()->orderBy('position_title')
            ->pluck('position_title')->filter()->values();
        $total = PlantillaRecord::count();

        return view('all-data.index', compact('records', 'offices', 'positions', 'total'));
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function create()
    {
        $plantilla = null; // so the shared form partial knows we're creating
        $offices = PlantillaRecord::distinct()->orderBy('organizational_unit')
            ->pluck('organizational_unit')->filter()->values();
        return view('all-data.create', compact('offices'));
    }

    // ── Store ─────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $data = $request->validate($this->validationRules());

        // Checkboxes default to false when unchecked
        $data['is_pwd'] = $request->boolean('is_pwd');
        $data['abolished'] = $request->boolean('abolished');
        $data['dissolved'] = $request->boolean('dissolved');
        $data['is_apprehended'] = $request->boolean('is_apprehended');
        $data['is_admin_charge'] = $request->boolean('is_admin_charge');
        $data['is_health_worker'] = $request->boolean('is_health_worker');

        // Clear dates if not checked
        if (!$data['is_apprehended']) {
            $data['apprehended_from'] = null;
        }
        if (!$data['is_admin_charge']) {
            $data['admin_charge_from'] = null;
            $data['admin_charge_to'] = null;
            $data['admin_charges'] = null;
        } else {
            // Clean up empty admin charges from array
            if (isset($data['admin_charges']) && is_array($data['admin_charges'])) {
                $cleanedCharges = [];
                foreach ($data['admin_charges'] as $charge) {
                    if (!empty($charge['from']) || !empty($charge['to']) || !empty($charge['type'])) {
                        $cleanedCharges[] = $charge;
                    }
                }
                $data['admin_charges'] = empty($cleanedCharges) ? null : $cleanedCharges;
            }
        }

        // Smart vacant logic:
        // - If no employee name is provided → force vacant (position has no holder)
        // - If a name is provided → force NOT vacant (position is filled), even if checkbox was accidentally ticked
        $hasEmployee = !empty(trim($data['last_name'] ?? '')) || !empty(trim($data['first_name'] ?? ''));
        $data['is_vacant'] = !$hasEmployee;

        // Auto-Calculate Annual Salary from Salary Grades if blank
        if (
            !empty($data['salary_grade']) && !empty($data['step']) &&
            (empty($data['actual_annual_salary']) || empty($data['authorized_annual_salary']))
        ) {
            $monthlySalary = \App\Models\SalaryGrade::getRate($data['salary_grade'], $data['step']);
            if ($monthlySalary > 0) {
                if (empty($data['actual_annual_salary'])) {
                    $data['actual_annual_salary'] = round($monthlySalary * 12, 2);
                }
                if (empty($data['authorized_annual_salary'])) {
                    $data['authorized_annual_salary'] = round($monthlySalary * 12, 2);
                }
            }
        }

        // Date Harmonization for Step Increments (NOSI/NOLP)
        if (!empty($data['date_original_appointment'])) {
            if (empty($data['date_last_promotion'])) {
                $data['date_last_promotion'] = $data['date_original_appointment'];
            }
            if (!array_key_exists('date_last_nolp', $data) || empty($data['date_last_nolp'])) {
                $data['date_last_nolp'] = $data['date_original_appointment'];
            }
        }

        // Auto-generate employee code if blank
        if (empty($data['employee_code']) && !empty($data['last_name']) && !empty($data['date_of_birth'])) {
            $data['employee_code'] = \App\Models\PlantillaRecord::generateEmployeeCode($data['last_name'], $data['date_of_birth']);
        }

        // Check if a record with this item number already exists
        $existing = PlantillaRecord::where('item', $data['item'])->first();

        if ($existing) {
            if ($existing->is_vacant) {
                // Reuse the vacant slot — update instead of insert
                $existing->update($data);
                $record = $existing;
                $action = 'Filled Vacant Position';
                $desc = $hasEmployee
                    ? 'Assigned "' . trim($record->first_name . ' ' . $record->last_name) . '" to previously vacant Item: ' . $record->item
                    : 'Re-registered Item ' . $record->item . ' as Vacant';
            } else {
                // Item is already occupied — block the creation
                return back()
                    ->withInput()
                    ->withErrors(['item' => 'Item number "' . $data['item'] . '" is already assigned to an active employee. Please use a different item number or edit the existing record.']);
            }
        } else {
            $record = PlantillaRecord::create($data);
            $action = $hasEmployee ? 'Created Record' : 'Added Vacant Position';
            $desc = $hasEmployee
                ? 'Created plantilla record for "' . trim($record->first_name . ' ' . $record->last_name) . '" (Item: ' . $record->item . ')'
                : 'Added new vacant position slot for Item: ' . $record->item;
        }

        if (Auth::check()) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'description' => $desc,
            ]);
        }

        return redirect()->route('all-data.index')
            ->with('success', 'Record saved successfully.');
    }

    // ── Edit ──────────────────────────────────────────────────────────────────

    public function edit(PlantillaRecord $allDatum)
    {
        $offices = PlantillaRecord::distinct()->orderBy('organizational_unit')
            ->pluck('organizational_unit')->filter()->values();
        return view('all-data.edit', ['plantilla' => $allDatum, 'offices' => $offices]);
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function update(Request $request, PlantillaRecord $allDatum)
    {
        $data = $request->validate($this->validationRules($allDatum->id));

        $data['is_pwd'] = $request->boolean('is_pwd');
        $data['abolished'] = $request->boolean('abolished');
        $data['dissolved'] = $request->boolean('dissolved');
        $data['is_apprehended'] = $request->boolean('is_apprehended');
        $data['is_admin_charge'] = $request->boolean('is_admin_charge');
        $data['is_health_worker'] = $request->boolean('is_health_worker');

        // Clear dates if not checked
        if (!$data['is_apprehended']) {
            $data['apprehended_from'] = null;
        }
        if (!$data['is_admin_charge']) {
            $data['admin_charge_from'] = null;
            $data['admin_charge_to'] = null;
            $data['admin_charges'] = null;
        } else {
            // Clean up empty admin charges from array
            if (isset($data['admin_charges']) && is_array($data['admin_charges'])) {
                $cleanedCharges = [];
                foreach ($data['admin_charges'] as $charge) {
                    if (!empty($charge['from']) || !empty($charge['to']) || !empty($charge['type'])) {
                        $cleanedCharges[] = $charge;
                    }
                }
                $data['admin_charges'] = empty($cleanedCharges) ? null : $cleanedCharges;
            }
        }

        // Smart vacant logic: derive is_vacant from whether an employee name exists.
        // This prevents inconsistency where a filled record is marked vacant (or vice versa).
        $hasEmployee = !empty(trim($data['last_name'] ?? '')) || !empty(trim($data['first_name'] ?? ''));
        $data['is_vacant'] = !$hasEmployee;

        // Auto-Process Separation/Retirement if requested
        if ($request->boolean('process_separation')) {
            $sepType = $data['nature_of_separation'] ?? 'SEPARATED';
            $sepDate = $data['date_separated'] ?? now()->toDateString();
            
            $formerName = trim(
                strtoupper($data['last_name'] ?? $allDatum->last_name ?? '') . ', ' .
                ($data['first_name'] ?? $allDatum->first_name ?? '') . ' ' .
                ($data['middle_name'] ?? $allDatum->middle_name ?? '')
            );
            
            $dobStr = !empty($data['date_of_birth']) ? \Carbon\Carbon::parse($data['date_of_birth'])->format('m/d/Y') : 'N/A';
            $sgStr = $data['salary_grade'] ?? $allDatum->salary_grade ?? '?';
            $stepStr = $data['step'] ?? $allDatum->step ?? '?';
            $tinStr = $data['tin'] ?? $allDatum->tin ?? 'N/A';

            $annotation = "{$sepType} effective {$sepDate}. "
                        . "Former employee: {$formerName}. "
                        . "DOB: {$dobStr}. SG-{$sgStr} Step {$stepStr}. TIN: {$tinStr}.";

            $existing = $data['comment_annotation'] ?? $allDatum->comment_annotation;
            $data['comment_annotation'] = $existing ? $existing . "\n\n" . $annotation : $annotation;

            // Clear employee fields to auto-declare vacant
            $data['last_name'] = null;
            $data['first_name'] = null;
            $data['middle_name'] = null;
            $data['sex'] = null;
            $data['date_of_birth'] = null;
            $data['tin'] = null;
            $data['gsis_bp_number'] = null;
            $data['umid'] = null;
            $data['employee_code'] = null;
            $data['is_vacant'] = true;
            
            // Set separation columns
            $data['nature_of_separation'] = $sepType;
            $data['date_separated'] = $sepDate;
            
            // If it's retirement, also set retired_at for the Retirement history
            if (stripos($sepType, 'retire') !== false) {
                $data['retired_at'] = $sepDate;
            }
        }

        // Auto-Calculate Annual Salary from Salary Grades if blank
        if (
            !empty($data['salary_grade']) && !empty($data['step']) &&
            (empty($data['actual_annual_salary']) || empty($data['authorized_annual_salary']))
        ) {
            $monthlySalary = \App\Models\SalaryGrade::getRate($data['salary_grade'], $data['step']);
            if ($monthlySalary > 0) {
                if (empty($data['actual_annual_salary'])) {
                    $data['actual_annual_salary'] = round($monthlySalary * 12, 2);
                }
                if (empty($data['authorized_annual_salary'])) {
                    $data['authorized_annual_salary'] = round($monthlySalary * 12, 2);
                }
            }
        }

        // Date Harmonization for Step Increments (NOSI/NOLP)
        if (!empty($data['date_original_appointment'])) {
            if (empty($data['date_last_promotion'])) {
                $data['date_last_promotion'] = $data['date_original_appointment'];
            }
            if (!array_key_exists('date_last_nolp', $data) || empty($data['date_last_nolp'])) {
                $data['date_last_nolp'] = $data['date_original_appointment'];
            }
        }

        // Auto-generate employee code if blank
        if (empty($data['employee_code']) && !empty($data['last_name']) && !empty($data['date_of_birth'])) {
            $data['employee_code'] = \App\Models\PlantillaRecord::generateEmployeeCode($data['last_name'], $data['date_of_birth']);
        }

        $allDatum->update($data);

        if (Auth::check()) {
            $label = $hasEmployee
                ? '"' . trim($allDatum->first_name . ' ' . $allDatum->last_name) . '"'
                : 'Vacant slot';
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Modified Data',
                'description' => 'Updated plantilla record for ' . $label . ' (Item: ' . $allDatum->item . ')',
            ]);
        }

        return redirect()->route('all-data.index')
            ->with('success', 'Record updated successfully.');
    }

    // ── Destroy ───────────────────────────────────────────────────────────────

    public function destroy(PlantillaRecord $allDatum)
    {
        $name = trim($allDatum->first_name . ' ' . $allDatum->last_name);
        $item = $allDatum->item;

        // Capture a full snapshot of the employee's data BEFORE clearing it,
        // so the "Undo" feature can restore it from the activity log.
        $snapshot = [
            'plantilla_record_id' => $allDatum->id,
            'last_name' => $allDatum->last_name,
            'first_name' => $allDatum->first_name,
            'middle_name' => $allDatum->middle_name,
            'sex' => $allDatum->sex,
            'date_of_birth' => $allDatum->date_of_birth?->format('Y-m-d'),
            'tin' => $allDatum->tin,
            'date_original_appointment' => $allDatum->date_original_appointment?->format('Y-m-d'),
            'date_last_promotion' => $allDatum->date_last_promotion?->format('Y-m-d'),
            'date_last_nolp' => $allDatum->date_last_nolp?->format('Y-m-d'),
            'employment_status' => $allDatum->employment_status,
            'civil_service_eligibility' => $allDatum->civil_service_eligibility,
            'comment_annotation' => $allDatum->comment_annotation,
            'gsis_bp_number' => $allDatum->gsis_bp_number,
            'umid' => $allDatum->umid,
            'is_pwd' => $allDatum->is_pwd,
            'indigenous_people' => $allDatum->indigenous_people,
            'solo_parent' => $allDatum->solo_parent,
            'is_apprehended' => $allDatum->is_apprehended,
            'is_admin_charge' => $allDatum->is_admin_charge,
            'apprehended_from' => $allDatum->apprehended_from?->format('Y-m-d'),
            'admin_charge_from' => $allDatum->admin_charge_from?->format('Y-m-d'),
            'admin_charge_to' => $allDatum->admin_charge_to?->format('Y-m-d'),
            'lwop' => $allDatum->lwop,
            'retired_at' => $allDatum->retired_at?->format('Y-m-d'),
        ];

        // Vacate the position — clear personal/employment info, keep the slot
        $allDatum->update([
            'is_vacant' => true,
            'last_name' => null,
            'first_name' => null,
            'middle_name' => null,
            'sex' => null,
            'date_of_birth' => null,
            'tin' => null,
            'date_original_appointment' => null,
            'date_last_promotion' => null,
            'date_last_nolp' => null,
            'employment_status' => null,
            'civil_service_eligibility' => null,
            'comment_annotation' => null,
            'gsis_bp_number' => null,
            'umid' => null,
            'is_pwd' => false,
            'indigenous_people' => null,
            'solo_parent' => null,
            'is_apprehended' => false,
            'is_admin_charge' => false,
            'apprehended_from' => null,
            'admin_charge_from' => null,
            'admin_charge_to' => null,
            'lwop' => null,
            'retired_at' => null,
        ]);

        if (Auth::check()) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Vacated Position',
                'description' => 'Removed employee "' . $name . '" and marked Item ' . $item . ' as Vacant',
                'snapshot' => $snapshot,
            ]);
        }

        return redirect()->route('all-data.index')
            ->with('success', 'Employee "' . $name . '" removed. Position ' . $item . ' is now Vacant. You can undo this from Activity Logs.');
    }

    // ── Export helpers ────────────────────────────────────────────────────────

    private function buildExportQuery(Request $request)
    {
        $query = PlantillaRecord::query()
            ->orderBy('organizational_unit')
            ->orderBy('item');

        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('last_name', 'like', "%{$term}%")
                    ->orWhere('first_name', 'like', "%{$term}%")
                    ->orWhere('item', 'like', "%{$term}%")
                    ->orWhere('position_title', 'like', "%{$term}%")
                    ->orWhere('organizational_unit', 'like', "%{$term}%")
                    ->orWhere('tin', 'like', "%{$term}%");
            });
        }
        if ($request->filled('office')) {
            $query->where('organizational_unit', $request->input('office'));
        }
        if ($request->filled('status')) {
            $statusMap = [
                'P' => ['P', 'Permanent'],
                'CT' => ['CT', 'Co-Terminous', 'Coterminous'],
                'E' => ['E', 'Elected'],
                'Casual' => ['Casual', 'Cas'],
                'JO' => ['JO', 'Job Order', 'J.O.'],
            ];
            $s = $request->input('status');
            if (isset($statusMap[$s])) {
                $query->whereIn('employment_status', $statusMap[$s]);
            }
        }
        if ($request->filled('sex')) {
            $query->where('sex', $request->input('sex'));
        }
        if ($request->filled('vacant')) {
            $vacant = $request->input('vacant');
            if ($vacant === 'vacant') {
                $query->where('is_vacant', true);
            } elseif ($vacant === 'vacant_funded') {
                $query->where('is_vacant', true)->where('abolished', false)->where('dissolved', false);
            } elseif ($vacant === 'vacant_unfunded') {
                $query->where(function($q) {
                    $q->where(function($q2) {
                        $q2->where('is_vacant', true)
                           ->where(function($q3) {
                               $q3->where('abolished', true)->orWhere('dissolved', true);
                           });
                    })->orWhere(function($q2) {
                        $q2->where(function($q3) {
                            $q3->whereNull('authorized_annual_salary')->orWhere('authorized_annual_salary', 0);
                        })->where(function($q3) {
                            $q3->whereNull('actual_annual_salary')->orWhere('actual_annual_salary', 0);
                        });
                    });
                });
            } elseif ($vacant === 'filled') {
                $query->where('is_vacant', false);
            }
        }
        if ($request->boolean('pwd')) {
            $query->where('is_pwd', true);
        }
        if ($request->boolean('ip')) {
            $query->whereNotNull('indigenous_people')->where('indigenous_people', '!=', '');
        }
        if ($request->boolean('solo_parent')) {
            $query->whereNotNull('solo_parent')->where('solo_parent', '!=', '')->where('solo_parent', '!=', '-');
        }
        if ($request->boolean('abolished')) {
            $query->where('abolished', true);
        }
        return $query;
    }

    // ── Excel Export ──────────────────────────────────────────────────────────

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['search', 'office', 'status', 'sex', 'vacant', 'pwd', 'ip', 'solo_parent', 'abolished']);
        $columns = $request->input('columns', []);
        $filename = 'plantilla-all-data-' . now()->format('Ymd-His') . '.xlsx';

        if (Auth::check()) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Exported Data',
                'description' => 'Exported plantilla data to Excel'
            ]);
        }

        return Excel::download(new AllDataExport($filters, $columns), $filename);
    }

    // ── PDF Export ────────────────────────────────────────────────────────────

    public function exportPdf(Request $request)
    {
        // Raise limits – DomPDF rendering ~3,900 rows needs extra resources
        ini_set('memory_limit', '2048M');
        set_time_limit(600);

        $records = $this->buildExportQuery($request)->get();
        $filters = $request->only(['search', 'office', 'status', 'sex', 'vacant', 'pwd', 'ip', 'solo_parent', 'abolished']);
        $columns = $request->input('columns', []);

        $pdf = Pdf::loadView('exports.all-data-pdf', compact('records', 'filters', 'columns'))
            ->setPaper('a4', 'landscape')
            ->setOption('defaultFont', 'DejaVu Sans')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('chunkSize', 512);

        $filename = 'plantilla-all-data-' . now()->format('Ymd-His') . '.pdf';

        if (Auth::check()) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Exported Data',
                'description' => 'Exported plantilla data to PDF'
            ]);
        }

        return $pdf->download($filename);
    }

    // ── API for Dynamic Salary Fetching ───────────────────────────────────────

    public function getSalary($grade, $step)
    {
        $monthlySalary = \App\Models\SalaryGrade::getRate((int) $grade, (int) $step);
        if ($monthlySalary > 0) {
            return response()->json([
                'monthly_salary' => $monthlySalary,
                'annual_salary' => round($monthlySalary * 12, 2)
            ]);
        }
        return response()->json(['error' => 'Salary not found'], 404);
    }

    public function getItemsByOffice(Request $request)
    {
        $office = $request->query('office');
        if (!$office)
            return response()->json([]);

        // Return full details for each vacant item so the form can auto-fill position title & SG
        $items = \App\Models\PlantillaRecord::where('organizational_unit', $office)
            ->where('is_vacant', true)
            ->orderBy('item')
            ->get(['item', 'position_title', 'salary_grade']);

        return response()->json($items);
    }
    /**
     * Synchronizes all plantilla_records actual and authorized salaries based on
     * the current monthly rates in the salary_grades table (active schedule).
     */
    public function syncSalaries()
    {
        $activeSchedule = \App\Models\SalarySchedule::getActive();

        if ($activeSchedule) {
            $scheduleFilter = "sg.salary_schedule_id = {$activeSchedule->id}";
            $scheduleLabel = '"' . $activeSchedule->name . '"';
        } else {
            $scheduleFilter = "sg.salary_schedule_id IS NULL";
            $scheduleLabel = "legacy baseline";
        }

        // Use a raw UPDATE JOIN for maximum performance on 4k+ rows
        $query = "
            UPDATE plantilla_records pr
            INNER JOIN salary_grades sg
                ON pr.salary_grade = sg.grade AND pr.step = sg.step
                AND {$scheduleFilter}
            SET
                pr.actual_annual_salary      = (sg.monthly_salary * 12),
                pr.authorized_annual_salary  = (sg.monthly_salary * 12),
                pr.updated_at                = NOW()
            WHERE
                pr.deleted_at IS NULL
                AND (
                    pr.actual_annual_salary      != (sg.monthly_salary * 12)
                    OR pr.authorized_annual_salary != (sg.monthly_salary * 12)
                )
        ";

        try {
            $affected = \Illuminate\Support\Facades\DB::update($query);

            if (Auth::check()) {
                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'Modified Data',
                    'description' => "Synchronized salaries for {$affected} records using salary schedule {$scheduleLabel}",
                ]);
            }

            return back()->with('success', "Salary Synchronization successful! Updated {$affected} record(s) using schedule {$scheduleLabel}.");
        } catch (\Exception $e) {
            return back()->with('error', "Failed to synchronize salaries: " . $e->getMessage());
        }
    }
}
