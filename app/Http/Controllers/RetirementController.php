<?php

namespace App\Http\Controllers;

use App\Models\PlantillaRecord;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RetirementController extends Controller
{
    // Philippine compulsory retirement age
    const RETIREMENT_AGE = 65;

    /**
     * Show the retirement management page.
     * - Overdue: employees who are already ≥ 65 and not yet vacated
     * - Near: employees turning 65 within the next 12 months
     */
    public function index(Request $request)
    {
        $tab = $request->input('tab', 'overdue');

        // ── Overdue: filled, not vacant, retired_at is null, age >= 65 ────────
        $cutoffDate = now()->subYears(self::RETIREMENT_AGE)->toDateString();

        $overdueQuery = PlantillaRecord::query()
            ->where('is_vacant', false)
            ->where('abolished', false)
            ->whereNotIn('employment_status', ['JO'])
            ->whereNotNull('date_of_birth')
            ->whereNull('retired_at')
            ->where('date_of_birth', '<=', $cutoffDate)
            ->orderBy('date_of_birth');

        // ── Near: turning 65 within the next 12 months ────────────────────────
        $nearStart = now()->subYears(self::RETIREMENT_AGE)->addMonths(1)->toDateString();
        $nearEnd   = now()->subYears(self::RETIREMENT_AGE)->addMonths(12)->toDateString();

        $nearQuery = PlantillaRecord::query()
            ->where('is_vacant', false)
            ->where('abolished', false)
            ->whereNotIn('employment_status', ['JO'])
            ->whereNotNull('date_of_birth')
            ->whereNull('retired_at')
            ->whereBetween('date_of_birth', [$nearStart, $nearEnd])
            ->orderBy('date_of_birth');

        // ── Optional: aged 60+ but not yet 65 ────────────────────────
        // Born after the 65-year cutoff (so < 65) but before or on the 60-year cutoff
        $optionalStart = now()->subYears(self::RETIREMENT_AGE)->toDateString();
        $optionalEnd   = now()->subYears(60)->toDateString();

        $optionalQuery = PlantillaRecord::query()
            ->where('is_vacant', false)
            ->where('abolished', false)
            ->whereNotIn('employment_status', ['JO'])
            ->whereNotNull('date_of_birth')
            ->whereNull('retired_at')
            ->where('date_of_birth', '>', $optionalStart)
            ->where('date_of_birth', '<=', $optionalEnd)
            ->orderBy('date_of_birth', 'desc');

        $overdue = $overdueQuery->paginate(25, ['*'], 'overdue_page')->withQueryString();
        $near    = $nearQuery->paginate(25, ['*'], 'near_page')->withQueryString();
        $optional= $optionalQuery->paginate(25, ['*'], 'optional_page')->withQueryString();

        $stats = [
            'overdue' => $overdueQuery->count(),
            'near'    => $nearQuery->count(),
            'optional'=> $optionalQuery->count(),
            'total_processed' => PlantillaRecord::whereNotNull('retired_at')->count(),
        ];

        return view('retirement.index', compact('overdue', 'near', 'optional', 'stats', 'tab'));
    }

    /**
     * Mark a single plantilla record as vacant due to retirement.
     */
    public function process(Request $request, PlantillaRecord $plantilla)
    {
        if ($plantilla->is_vacant) {
            return back()->with('error', 'This position is already vacant.');
        }

        $formerName = trim(
            strtoupper($plantilla->last_name ?? '') . ', ' .
            ($plantilla->first_name ?? '') . ' ' .
            ($plantilla->middle_name ?? '')
        );

        $validated = $request->validate([
            'effective_date' => 'nullable|date'
        ]);
        $retirementDate = $validated['effective_date'] ?? now()->toDateString();
        $type = $request->input('retirement_type', 'COMPULSORY RETIREMENT');

        $annotation = "{$type} effective {$retirementDate}. "
                    . "Former employee: {$formerName}. "
                    . "DOB: " . ($plantilla->date_of_birth?->format('m/d/Y') ?? 'N/A') . ". "
                    . "SG-{$plantilla->salary_grade} Step {$plantilla->step}. "
                    . "TIN: " . ($plantilla->tin ?? 'N/A') . '.';

        $existing = $plantilla->remarks_annotation;
        $fullAnnotation = $existing
            ? $existing . "\n\n" . $annotation
            : $annotation;

        $plantilla->update([
            'is_vacant'          => true,
            'retired_at'         => $retirementDate,
            'last_name'          => null,
            'first_name'         => null,
            'middle_name'        => null,
            'sex'                => null,
            'date_of_birth'      => null,
            'tin'                => null,
            'gsis_bp_number'     => null,
            'umid'               => null,
            'remarks_annotation' => $fullAnnotation,
        ]);

        return back()->with('success',
            "Position marked vacant. Former employee: {$formerName} has been retired effective {$retirementDate}."
        );
    }

    /**
     * Show the history of all processed retirements.
     */
    public function history(Request $request)
    {
        $search = $request->input('search', '');
        $year   = $request->input('year', '');

        $query = PlantillaRecord::whereNotNull('retired_at')
            ->orderBy('retired_at', 'desc');

        if ($year) {
            $query->whereYear('retired_at', $year);
        }

        if ($search) {
            $term = '%' . $search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('office_department', 'like', $term)
                  ->orWhere('position_title', 'like', $term)
                  ->orWhere('remarks_annotation', 'like', $term);
            });
        }

        $records = $query->paginate(25)->withQueryString();

        // Get distinct years for the year filter dropdown
        $years = PlantillaRecord::whereNotNull('retired_at')
            ->selectRaw('YEAR(retired_at) as yr')
            ->distinct()
            ->orderByDesc('yr')
            ->pluck('yr');

        $totalRetired = PlantillaRecord::whereNotNull('retired_at')->count();

        return view('retirement.history', compact('records', 'years', 'totalRetired', 'search', 'year'));
    }

    /**
     * Update an already-processed retirement's details.
     */
    public function updateHistory(Request $request, PlantillaRecord $plantilla)
    {
        $validated = $request->validate([
            'retired_at' => 'required|date',
            'remarks_annotation' => 'nullable|string'
        ]);

        $plantilla->update($validated);

        return back()->with('success', 'Retirement historical details updated successfully.');
    }

    /**
     * Bulk-process all overdue retirements at once.
     */
    public function processAll()
    {
        $cutoffDate = now()->subYears(self::RETIREMENT_AGE)->toDateString();

        $records = PlantillaRecord::query()
            ->where('is_vacant', false)
            ->where('abolished', false)
            ->whereNotIn('employment_status', ['JO'])
            ->whereNotNull('date_of_birth')
            ->whereNull('retired_at')
            ->where('date_of_birth', '<=', $cutoffDate)
            ->get();

        if ($records->isEmpty()) {
            return back()->with('info', 'No employees are currently overdue for retirement.');
        }

        $retirementDate = now()->toDateString();
        $count = 0;

        foreach ($records as $plantilla) {
            $formerName = trim(
                strtoupper($plantilla->last_name ?? '') . ', ' .
                ($plantilla->first_name ?? '') . ' ' .
                ($plantilla->middle_name ?? '')
            );

            $annotation = "COMPULSORY RETIREMENT effective {$retirementDate}. "
                        . "Former employee: {$formerName}. "
                        . "DOB: " . ($plantilla->date_of_birth?->format('m/d/Y') ?? 'N/A') . ". "
                        . "SG-{$plantilla->salary_grade} Step {$plantilla->step}. "
                        . "TIN: " . ($plantilla->tin ?? 'N/A') . '.';

            $existing = $plantilla->remarks_annotation;
            $fullAnnotation = $existing ? $existing . "\n\n" . $annotation : $annotation;

            $plantilla->update([
                'is_vacant'          => true,
                'retired_at'         => $retirementDate,
                'last_name'          => null,
                'first_name'         => null,
                'middle_name'        => null,
                'sex'                => null,
                'date_of_birth'      => null,
                'tin'                => null,
                'gsis_bp_number'     => null,
                'umid'               => null,
                'remarks_annotation' => $fullAnnotation,
            ]);

            $count++;
        }

        return back()->with('success',
            "Bulk retirement complete. {$count} employee(s) retired and their positions marked as vacant."
        );
    }

    /**
     * Export retirement list (overdue + near) as PDF.
     */
    public function exportPdf(Request $request)
    {
        $tab = $request->input('tab', 'overdue');
        $cutoffDate = now()->subYears(self::RETIREMENT_AGE)->toDateString();

        if ($tab === 'near') {
            $nearStart = now()->subYears(self::RETIREMENT_AGE)->addMonths(1)->toDateString();
            $nearEnd   = now()->subYears(self::RETIREMENT_AGE)->addMonths(12)->toDateString();
            $list = PlantillaRecord::query()
                ->where('is_vacant', false)->where('abolished', false)
                ->whereNotIn('employment_status', ['JO'])
                ->whereNotNull('date_of_birth')->whereNull('retired_at')
                ->whereBetween('date_of_birth', [$nearStart, $nearEnd])
                ->orderBy('date_of_birth')->get();
            $title = 'Near Retirement (within 12 months)';

        } elseif ($tab === 'optional') {
            $optionalStart = $cutoffDate;
            $optionalEnd   = now()->subYears(60)->toDateString();
            $list = PlantillaRecord::query()
                ->where('is_vacant', false)->where('abolished', false)
                ->whereNotIn('employment_status', ['JO'])
                ->whereNotNull('date_of_birth')->whereNull('retired_at')
                ->where('date_of_birth', '>', $optionalStart)
                ->where('date_of_birth', '<=', $optionalEnd)
                ->orderBy('date_of_birth', 'desc')->get();
            $title = 'Optional to Retire (Ages 60–64)';

        } elseif ($tab === 'history') {
            $search = $request->input('search', '');
            $year   = $request->input('year', '');
            $query  = PlantillaRecord::whereNotNull('retired_at')->orderBy('retired_at', 'desc');
            if ($search) {
                $term = '%' . $search . '%';
                $query->where(fn($q) => $q->where('office_department', 'like', $term)
                    ->orWhere('position_title', 'like', $term)
                    ->orWhere('remarks_annotation', 'like', $term));
            }
            if ($year) {
                $query->whereYear('retired_at', $year);
            }
            $list  = $query->get();
            $title = 'Retirement History';

        } else {
            $list = PlantillaRecord::query()
                ->where('is_vacant', false)->where('abolished', false)
                ->whereNotIn('employment_status', ['JO'])
                ->whereNotNull('date_of_birth')->whereNull('retired_at')
                ->where('date_of_birth', '<=', $cutoffDate)
                ->orderBy('date_of_birth')->get();
            $title = 'Overdue for Retirement (Age ≥ 65)';
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.retirement-pdf', compact('list', 'title', 'tab'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('Retirement_' . ucfirst($tab) . '_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export retirement list as Excel.
     */
    public function exportExcel(Request $request)
    {
        $tab    = $request->input('tab', 'overdue');
        $search = $request->input('search', '');
        $year   = $request->input('year', '');
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\RetirementExport($tab, $search, $year),
            'Retirement_' . ucfirst($tab) . '_' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
