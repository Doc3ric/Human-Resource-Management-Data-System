<?php

namespace App\Http\Controllers;

use App\Models\PlantillaRecord;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Reports\Report1Export;
use App\Exports\Reports\Report2Export;
use App\Exports\Reports\Report3Export;
use App\Exports\Reports\Report4Export;
use App\Exports\Reports\Report5Export;
use App\Exports\Reports\Report6Export;

class PlantillaController extends Controller
{
    // The 7 appointment categories in display order
    const CATEGORIES = [
        'Elected',
        'Co-Terminous',
        'Permanent',
        'Casual',
        'Job Order',
        'Vacant Funded',
        'Vacant Unfunded',
    ];

    // Colours for each category badge
    const CAT_COLORS = [
        'Elected'         => 'bg-purple-100 text-purple-700',
        'Co-Terminous'    => 'bg-indigo-100 text-indigo-700',
        'Permanent'       => 'bg-green-100 text-green-700',
        'Casual'          => 'bg-amber-100 text-amber-700',
        'Job Order'       => 'bg-orange-100 text-orange-700',
        'Vacant Funded'   => 'bg-blue-100 text-blue-700',
        'Vacant Unfunded' => 'bg-gray-200 text-gray-600',
    ];

    /**
     * Returns true when both annual-salary fields are zero / null.
     * Such positions are treated as Vacant Unfunded automatically.
     */
    private function hasZeroSalary(PlantillaRecord $record): bool
    {
        $auth   = (float) ($record->authorized_annual_salary ?? 0);
        $actual = (float) ($record->actual_annual_salary     ?? 0);
        return $auth === 0.0 && $actual === 0.0;
    }

    private function resolveCategory(PlantillaRecord $record): string
    {
        // Zero-salary positions are always Vacant Unfunded
        if ($this->hasZeroSalary($record)) {
            return 'Vacant Unfunded';
        }

        if ($record->is_vacant) {
            return ($record->abolished || $record->dissolved)
                ? 'Vacant Unfunded'
                : 'Vacant Funded';
        }

        $s = strtolower(trim($record->employment_status ?? ''));

        if (in_array($s, ['elected', 'e']))                                          return 'Elected';
        if (in_array($s, ['co-terminous','coterminous','co terminous','ct']))         return 'Co-Terminous';
        if (in_array($s, ['permanent','p']))                                          return 'Permanent';
        if (in_array($s, ['casual','cas']))                                           return 'Casual';
        if (in_array($s, ['job order','jo','j.o.','job-order']))                     return 'Job Order';

        // Partial matches as fallback
        if (str_contains($s, 'elect'))      return 'Elected';
        if (str_contains($s, 'terminous'))  return 'Co-Terminous';
        if (str_contains($s, 'permanent'))  return 'Permanent';
        if (str_contains($s, 'casual'))     return 'Casual';
        if (str_contains($s, 'job'))        return 'Job Order';

        return 'Permanent'; // safe default
    }

    /**
     * Display Inventory of Personnel dashboard + grouped list.
     */
    public function index(Request $request)
    {
        // ── 1. STATS: Optimize with DB aggregates & caching ─────────────────────

        // Cache the heavy statistics for 5 minutes since they don't change every second
        $stats = cache()->remember('plantilla_stats_v2', 300, function () {
            // Only select necessary columns for stats to save memory
            $allRecords = PlantillaRecord::select(
                'is_vacant', 'employment_status', 'date_of_birth', 'sex', 
                'abolished', 'dissolved', 'authorized_annual_salary', 
                'actual_annual_salary', 'position_title', 'organizational_unit',
                'first_name', 'last_name', 'middle_name'
            )->get();
            $filledAll  = $allRecords->where('is_vacant', false);

            // Status counts
            $statusCounts = array_fill_keys(self::CATEGORIES, 0);
            foreach ($allRecords as $r) {
                $statusCounts[$this->resolveCategory($r)]++;
            }

            // Also include separate Job Orders from the job_orders table
            $jobOrderCount = \App\Models\JobOrder::count();
            $statusCounts['Job Order'] += $jobOrderCount;

            // Age ranges (filled employees with a known DOB)
            $ageRanges = ['21-30' => 0, '31-40' => 0, '41-50' => 0, '51-60' => 0, '61-65' => 0, 'Other' => 0];
            $nearRetirement = [];           // employees aged 61–65
            foreach ($filledAll as $r) {
                if (empty($r->date_of_birth)) continue;
                try {
                    $age = \Carbon\Carbon::parse($r->date_of_birth)->age;
                    if ($age >= 61 && $age <= 65)      { $ageRanges['61-65']++; $nearRetirement[] = clone $r; } // Clone to detach from big collection
                    elseif ($age >= 51 && $age <= 60)  { $ageRanges['51-60']++; }
                    elseif ($age >= 41 && $age <= 50)  { $ageRanges['41-50']++; }
                    elseif ($age >= 31 && $age <= 40)  { $ageRanges['31-40']++; }
                    elseif ($age >= 21 && $age <= 30)  { $ageRanges['21-30']++; }
                    else                               { $ageRanges['Other']++; }
                } catch (\Exception) {}
            }

            // Gender counts
            $genderCounts = ['M' => 0, 'F' => 0, 'Unknown' => 0];
            foreach ($filledAll as $r) {
                $sex = strtoupper(trim($r->sex ?? ''));
                if ($sex === 'M')      $genderCounts['M']++;
                elseif ($sex === 'F')  $genderCounts['F']++;
                else                   $genderCounts['Unknown']++;
            }

            // Vacant Funded — group by position_title with count
            $vacantFunded = $allRecords
                ->where('is_vacant', true)
                ->where('abolished', false)
                ->where('dissolved', false)
                ->groupBy('position_title')
                ->map(fn($g) => $g->count())
                ->sortDesc();

            // Vacant Unfunded — abolished/dissolved positions OR zero-salary records
            $vacantUnfunded = $allRecords
                ->filter(fn($r) =>
                    ($r->is_vacant && ($r->abolished || $r->dissolved))
                    || $this->hasZeroSalary($r)
                )
                ->groupBy('position_title')
                ->map(fn($g) => $g->count())
                ->sortDesc();

            return [
                'statusCounts' => $statusCounts,
                'ageRanges' => $ageRanges,
                'nearRetirement' => $nearRetirement, // Store array of objects
                'genderCounts' => $genderCounts,
                'vacantFunded' => $vacantFunded,
                'vacantUnfunded' => $vacantUnfunded,
                'totalAll' => $allRecords->count()
            ];
        });

        // Extract cached stats
        $statusCounts   = $stats['statusCounts'];
        $ageRanges      = $stats['ageRanges'];
        $nearRetirement = collect($stats['nearRetirement']); // Convert back to collection for view
        $genderCounts   = $stats['genderCounts'];
        $vacantFunded   = $stats['vacantFunded'];
        $vacantUnfunded = $stats['vacantUnfunded'];
        $totalAll       = $stats['totalAll'];

        // ── 2. FILTERED LIST: per-office accordion ─────────────────────────
        $query = PlantillaRecord::query()
            ->orderBy('organizational_unit')
            ->orderBy('item');

        $isFiltered = false;

        if ($request->filled('search')) {
            $isFiltered = true;
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('last_name',        'like', "%{$search}%")
                  ->orWhere('first_name',     'like', "%{$search}%")
                  ->orWhere('item',           'like', "%{$search}%")
                  ->orWhere('position_title', 'like', "%{$search}%");
            });
        }
        if ($request->filled('office')) {
            $isFiltered = true;
            $query->where('organizational_unit', $request->input('office'));
        }
        if ($request->filled('age_range')) {
            $isFiltered = true;
            $ageRange = $request->input('age_range');
            $parts = explode('-', $ageRange);
            if (count($parts) === 2) {
                $minAge = (int)$parts[0];
                $maxAge = (int)$parts[1];
                $now = \Carbon\Carbon::now();
                $minDate = $now->copy()->subYears($maxAge + 1)->addDay()->format('Y-m-d');
                $maxDate = $now->copy()->subYears($minAge)->format('Y-m-d');
                $query->whereDate('date_of_birth', '>=', $minDate)
                      ->whereDate('date_of_birth', '<=', $maxDate);
            }
        }
        if ($request->filled('category')) {
            $isFiltered = true;
            $cat = $request->input('category');
            if ($cat === 'Vacant Funded') {
                $query->where('is_vacant', true)->where('abolished', false)->where('dissolved', false);
            } elseif ($cat === 'Vacant Unfunded') {
                $query->where(function($q) {
                    // abolished/dissolved vacants
                    $q->where(function($q2) {
                        $q2->where('is_vacant', true)
                           ->where(function($q3) {
                               $q3->where('abolished', true)->orWhere('dissolved', true);
                           });
                    })
                    // OR zero-salary records
                    ->orWhere(function($q2) {
                        $q2->where(function($q3) {
                            $q3->whereNull('authorized_annual_salary')
                               ->orWhere('authorized_annual_salary', 0);
                        })->where(function($q3) {
                            $q3->whereNull('actual_annual_salary')
                               ->orWhere('actual_annual_salary', 0);
                        });
                    });
                });
            } else {
                $statusMap = [
                    'Elected'      => ['E','Elected'],
                    'Co-Terminous' => ['CT','Co-Terminous','Coterminous'],
                    'Permanent'    => ['P','Permanent'],
                    'Casual'       => ['Casual','Cas'],
                    'Job Order'    => ['JO','Job Order','J.O.'],
                ];
                $query->where('is_vacant', false)->whereIn('employment_status', $statusMap[$cat] ?? [$cat]);
            }
        }
        if ($request->filled('sex')) {
            $isFiltered = true;
            $query->where('sex', strtoupper($request->input('sex')));
        }
        if ($request->filled('position')) {
            $isFiltered = true;
            $query->where('position_title', $request->input('position'));
        }

        $filtered = $query->get();

        $emptySlots  = array_fill_keys(self::CATEGORIES, []);
        $grouped     = [];

        foreach ($filtered as $record) {
            $office = $record->organizational_unit ?: 'Unassigned';
            $cat    = $this->resolveCategory($record);
            if (!isset($grouped[$office])) $grouped[$office] = $emptySlots;
            $grouped[$office][$cat][] = $record;
        }
        ksort($grouped);

        // Cache offices list since it rarely changes completely
        $offices = cache()->remember('plantilla_offices_list', 3600, function() {
            return PlantillaRecord::distinct()
                ->orderBy('organizational_unit')
                ->pluck('organizational_unit')
                ->filter()->values();
        });

        // Distinct position titles for the Position filter dropdown
        $positions = cache()->remember('plantilla_positions_list', 3600, function() {
            return PlantillaRecord::distinct()
                ->orderBy('position_title')
                ->pluck('position_title')
                ->filter()->values();
        });

        return view('plantilla.index', [
            'grouped'        => $grouped,
            'offices'        => $offices,
            'positions'      => $positions,
            'categories'     => self::CATEGORIES,
            'catColors'      => self::CAT_COLORS,
            'totalAll'       => $totalAll,
            // Stats
            'statusCounts'   => $statusCounts,
            'ageRanges'      => $ageRanges,
            'nearRetirement' => $nearRetirement,
            'genderCounts'   => $genderCounts,
            'vacantFunded'   => $vacantFunded,
            'vacantUnfunded' => $vacantUnfunded,
            'isFiltered'     => $isFiltered, // Pass this to view to show a warning if truncated
            'pwdCount'       => PlantillaRecord::where('is_pwd', true)->where('is_vacant', false)->count(),
            'ipCount'        => PlantillaRecord::whereNotNull('indigenous_people')->where('indigenous_people', '!=', '')->where('is_vacant', false)->count(),
        ]);
    }

    /**
     * Display the PWD Personnel Report
     */
    public function pwdReport()
    {
        $pwdRecords = PlantillaRecord::where('is_pwd', true)
                        ->where('is_vacant', false)
                        ->orderBy('organizational_unit')
                        ->orderBy('last_name')
                        ->get();
                        
        return view('plantilla.pwd-report', compact('pwdRecords'));
    }

    /**
     * Export PWD Personnel Report as PDF.
     */
    public function exportPwdPdf()
    {
        $pwdRecords = PlantillaRecord::where('is_pwd', true)
                        ->where('is_vacant', false)
                        ->orderBy('organizational_unit')
                        ->orderBy('last_name')
                        ->get();

        $pdf = Pdf::loadView('exports.pwd-pdf', compact('pwdRecords'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('PWD_Personnel_Report_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export PWD Personnel Report as Excel.
     */
    public function exportPwdExcel()
    {
        return Excel::download(
            new \App\Exports\PwdExport(),
            'PWD_Personnel_Report_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Display the IP Personnel Report
     */
    public function ipReport()
    {
        $ipRecords = PlantillaRecord::whereNotNull('indigenous_people')
                        ->where('indigenous_people', '!=', '')
                        ->where('is_vacant', false)
                        ->orderBy('organizational_unit')
                        ->orderBy('last_name')
                        ->get();
                        
        return view('plantilla.ip-report', compact('ipRecords'));
    }

    /**
     * Export IP Personnel Report as PDF.
     */
    public function exportIpPdf()
    {
        $ipRecords = PlantillaRecord::whereNotNull('indigenous_people')
                        ->where('indigenous_people', '!=', '')
                        ->where('is_vacant', false)
                        ->orderBy('organizational_unit')
                        ->orderBy('last_name')
                        ->get();

        $pdf = Pdf::loadView('exports.ip-pdf', compact('ipRecords'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('IP_Personnel_Report_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export IP Personnel Report as Excel.
     */
    public function exportIpExcel()
    {
        return Excel::download(
            new \App\Exports\IpExport(),
            'IP_Personnel_Report_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Display the Position Personnel Report (MFMP 001-2023 format).
     * ?position=URL-encoded+position+title
     */
    public function positionReport(Request $request)
    {
        $positions = PlantillaRecord::distinct()
            ->orderBy('position_title')
            ->pluck('position_title')
            ->filter()->values();

        $positionInput = $request->input('position', []);
        $position = is_array($positionInput) ? array_filter($positionInput) : (trim($positionInput) ? [$positionInput] : []);
        $records  = collect();

        if (!empty($position)) {
            $records = PlantillaRecord::whereIn('position_title', $position)
                ->where('is_vacant', false)
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();
        }

        return view('plantilla.position-report', compact('records', 'position', 'positions'));
    }

    /**
     * Export Position Report as PDF.
     */
    public function exportPositionReportPdf(Request $request)
    {
        $positionInput = $request->input('position', []);
        $position = is_array($positionInput) ? array_filter($positionInput) : (trim($positionInput) ? [$positionInput] : []);

        $records  = PlantillaRecord::whereIn('position_title', $position)
            ->where('is_vacant', false)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $pdf = Pdf::loadView('exports.position-report-pdf', compact('records', 'position'))
            ->setPaper('a4', 'landscape');

        $slug = count($position) === 1 ? \Illuminate\Support\Str::slug($position[0]) : 'various';
        return $pdf->download('Position_Report_' . $slug . '_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export Position Report as Excel.
     */
    public function exportPositionReportExcel(Request $request)
    {
        $positionInput = $request->input('position', []);
        $position = is_array($positionInput) ? array_filter($positionInput) : (trim($positionInput) ? [$positionInput] : []);
        
        $slug = count($position) === 1 ? \Illuminate\Support\Str::slug($position[0]) : 'various';

        return Excel::download(
            new \App\Exports\PositionReportExport($position),
            'Position_Report_' . $slug . '_' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Export Vacant Positions list as PDF.
     * ?type=funded|unfunded
     */
    public function exportVacantPdf(Request $request)
    {
        $type = $request->input('type', 'funded'); // 'funded' or 'unfunded'

        if ($type === 'unfunded') {
            $records = PlantillaRecord::where(function($q) {
                $q->where(function($q2) {
                    $q2->where('is_vacant', true)
                       ->where(function($q3) { $q3->where('abolished', true)->orWhere('dissolved', true); });
                })->orWhere(function($q2) {
                    $q2->where(function($q3) {
                        $q3->whereNull('authorized_annual_salary')->orWhere('authorized_annual_salary', 0);
                    })->where(function($q3) {
                        $q3->whereNull('actual_annual_salary')->orWhere('actual_annual_salary', 0);
                    });
                });
            })->orderBy('position_title')->get();
            $title = 'Vacant Unfunded Positions';
        } else {
            $records = PlantillaRecord::where('is_vacant', true)
                ->where('abolished', false)->where('dissolved', false)
                ->orderBy('position_title')->get();
            $title = 'Vacant Funded Positions';
        }

        $pdf = Pdf::loadView('exports.vacant-pdf', compact('records', 'title', 'type'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('Vacant_' . ucfirst($type) . '_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export Vacant Positions list as Excel.
     * ?type=funded|unfunded
     */
    public function exportVacantExcel(Request $request)
    {
        $type = $request->input('type', 'funded');
        $filename = 'Vacant_' . ucfirst($type) . '_' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new \App\Exports\VacantExport($type), $filename);
    }

    /**
     * Show a detail view of all vacant-funded records for a single position title.
     * ?position=URL-encoded+position+title
     */
    public function vacantFundedDetail(Request $request)
    {
        $position = $request->input('position', '');

        $records = PlantillaRecord::where('is_vacant', true)
            ->where('abolished', false)
            ->where('dissolved', false)
            ->where('position_title', $position)
            ->orderBy('organizational_unit')
            ->orderBy('item')
            ->get();

        return view('plantilla.vacant-funded-detail', compact('records', 'position'));
    }

    /**
     * Export a single vacant-funded position as Excel.
     * ?position=URL-encoded+position+title
     */
    public function exportVacantFundedDetailExcel(Request $request)
    {
        $position = $request->input('position', '');
        $slug = \Illuminate\Support\Str::slug($position);
        $filename = 'Vacant_Funded_' . $slug . '_' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new \App\Exports\VacantFundedPositionExport($position),
            $filename
        );
    }

    /**
     * Export a single vacant-funded position as PDF.
     * ?position=URL-encoded+position+title
     */
    public function exportVacantFundedDetailPdf(Request $request)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(600);

        $position = $request->input('position', '');
        $slug = \Illuminate\Support\Str::slug($position);
        $filename = 'Vacant_Funded_' . $slug . '_' . now()->format('Y-m-d') . '.pdf';

        $records = PlantillaRecord::where('is_vacant', true)
            ->where('abolished', false)
            ->where('dissolved', false)
            ->where('position_title', $position)
            ->orderBy('organizational_unit')
            ->orderBy('item')
            ->get();

        $pdf = Pdf::loadView('exports.vacant-funded-position-pdf', compact('records', 'position'))
            ->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

    /**
     * Show a detail view of all vacant-unfunded records for a single position title.
     * ?position=URL-encoded+position+title
     */
    public function vacantUnfundedDetail(Request $request)
    {
        $position = $request->input('position', '');

        $records = PlantillaRecord::where(function($q) {
                // abolished/dissolved vacants
                $q->where(function($q2) {
                    $q2->where('is_vacant', true)
                       ->where(function($q3) {
                           $q3->where('abolished', true)->orWhere('dissolved', true);
                       });
                })
                // OR zero-salary records
                ->orWhere(function($q2) {
                    $q2->where(function($q3) {
                        $q3->whereNull('authorized_annual_salary')
                           ->orWhere('authorized_annual_salary', 0);
                    })->where(function($q3) {
                        $q3->whereNull('actual_annual_salary')
                           ->orWhere('actual_annual_salary', 0);
                    });
                });
            })
            ->where('position_title', $position)
            ->orderBy('organizational_unit')
            ->orderBy('item')
            ->get();

        return view('plantilla.vacant-unfunded-detail', compact('records', 'position'));
    }

    /**
     * Export a single vacant-unfunded position as Excel.
     * ?position=URL-encoded+position+title
     */
    public function exportVacantUnfundedDetailExcel(Request $request)
    {
        $position = $request->input('position', '');
        $slug = \Illuminate\Support\Str::slug($position);
        $filename = 'Vacant_Unfunded_' . $slug . '_' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new \App\Exports\VacantUnfundedPositionExport($position),
            $filename
        );
    }

    /**
     * Export a single vacant-unfunded position as PDF.
     * ?position=URL-encoded+position+title
     */
    public function exportVacantUnfundedDetailPdf(Request $request)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(600);

        $position = $request->input('position', '');
        $slug = \Illuminate\Support\Str::slug($position);
        $filename = 'Vacant_Unfunded_' . $slug . '_' . now()->format('Y-m-d') . '.pdf';

        $records = PlantillaRecord::where(function($q) {
                $q->where(function($q2) {
                    $q2->where('is_vacant', true)
                       ->where(function($q3) {
                           $q3->where('abolished', true)->orWhere('dissolved', true);
                       });
                })->orWhere(function($q2) {
                    $q2->where(function($q3) {
                        $q3->whereNull('authorized_annual_salary')
                           ->orWhere('authorized_annual_salary', 0);
                    })->where(function($q3) {
                        $q3->whereNull('actual_annual_salary')
                           ->orWhere('actual_annual_salary', 0);
                    });
                });
            })
            ->where('position_title', $position)
            ->orderBy('organizational_unit')
            ->orderBy('item')
            ->get();

        $pdf = Pdf::loadView('exports.vacant-unfunded-position-pdf', compact('records', 'position'))
            ->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }


    /**
     * Statistical Personnel Reports (Reports 1–5 from CSC form)
     */
    public function reports(Request $request)
    {
        $asOf = $request->input('as_of', now()->format('Y-m'));
        $asOfDate = \Carbon\Carbon::parse($asOf . '-01');
        $year = $asOfDate->year;
        $month = $asOfDate->month;

        // ── All non-vacant filled records ──────────────────────────────────────
        $filled = PlantillaRecord::where('is_vacant', false)
            ->whereNull('nature_of_separation')
            ->orderBy('organizational_unit')
            ->orderBy('last_name')
            ->get();

        // ── REPORT 1: Inventory by Office × Appointment Type ──────────────────
        // Columns: Office | Permanent | Elected | Co-Terminous | Casual | Job Order | Total
        $report1 = [];
        foreach ($filled as $r) {
            $office = $r->organizational_unit ?: 'Unassigned';
            $cat    = $this->resolveCategory($r);
            if (!isset($report1[$office])) {
                $report1[$office] = [
                    'Elected' => 0, 'Co-Terminous' => 0, 'Permanent' => 0,
                    'Casual' => 0, 'Job Order' => 0, 'Total' => 0
                ];
            }
            if (array_key_exists($cat, $report1[$office])) {
                $report1[$office][$cat]++;
            }
            $report1[$office]['Total']++;
        }
        ksort($report1);

        // ── REPORT 2: Inventory by Status + Gender + Age (all filled employees) ─
        $report2 = $filled->map(function ($r) {
            $age = $r->date_of_birth
                ? \Carbon\Carbon::parse($r->date_of_birth)->age
                : null;
            return [
                'record'  => $r,
                'cat'     => $this->resolveCategory($r),
                'age'     => $age,
            ];
        })->groupBy(fn($item) => $item['cat']);

        // ── REPORT 3: Newly Hired / Promoted / Demoted (filtered by year) ──────
        $report3 = PlantillaRecord::where('is_vacant', false)
            ->whereNull('nature_of_separation')
            ->whereNotNull('nature_of_appointment')
            ->where(function($q) use ($year) {
                $q->whereYear('date_original_appointment', $year)
                  ->orWhereYear('date_last_promotion', $year);
            })
            ->orderBy('organizational_unit')
            ->orderBy('last_name')
            ->get()
            ->each(function($r) {
                $r->resolved_category = $this->resolveCategory($r);
            });

        // ── REPORT 4: List of Retirees ─────────────────────────────────────────
        $report4 = PlantillaRecord::whereYear('date_separated', $year)
            ->where('nature_of_separation', 'Retired')
            ->orderBy('organizational_unit')
            ->orderBy('last_name')
            ->get()
            ->map(function ($r) {
                $yearsInService = $r->date_original_appointment
                    ? \Carbon\Carbon::parse($r->date_original_appointment)
                        ->diffInYears($r->date_separated ?? now())
                    : null;
                return ['record' => $r, 'years_in_service' => $yearsInService];
            });

        // ── REPORT 5: Terminated/Separated Employees ───────────────────────────
        $report5 = PlantillaRecord::whereYear('date_separated', $year)
            ->whereNotNull('nature_of_separation')
            ->orderBy('organizational_unit')
            ->orderBy('last_name')
            ->get();

        // ── REPORT 6: By Employment Status grouped by Office ───────────────────
        // Office | Status | Total | Employee list | Gender
        $report6 = [];
        foreach ($filled as $r) {
            $office = $r->organizational_unit ?: 'Unassigned';
            $cat    = $this->resolveCategory($r);
            if (!isset($report6[$office][$cat])) {
                $report6[$office][$cat] = [];
            }
            $report6[$office][$cat][] = $r;
        }
        ksort($report6);

        $offices = PlantillaRecord::distinct()
            ->orderBy('organizational_unit')
            ->pluck('organizational_unit')
            ->filter()->values();

        return view('plantilla.reports', compact(
            'report1', 'report2', 'report3', 'report4', 'report5', 'report6',
            'asOf', 'year', 'month', 'offices'
        ));
    }

    /**
     * Export a single statistical report as PDF.
     * Route: GET plantilla/reports/export/pdf/{report}?as_of=YYYY-MM
     */
    public function exportReportPdf(Request $request, int $report)
    {
        $data = $this->buildReportData($request);

        $view = "exports.reports.report{$report}-pdf";
        $filename = "Report{$report}_" . str_replace('-', '', $data['asOf']) . ".pdf";

        $pdf = Pdf::loadView($view, $data)
            ->setPaper('a4', $report === 1 || $report === 6 ? 'landscape' : 'portrait');

        return $pdf->download($filename);
    }

    /**
     * Export a single statistical report as Excel.
     * Route: GET plantilla/reports/export/excel/{report}?as_of=YYYY-MM
     */
    public function exportReportExcel(Request $request, int $report)
    {
        $data = $this->buildReportData($request);
        $filename = "Report{$report}_" . str_replace('-', '', $data['asOf']) . ".xlsx";

        $exportClasses = [
            1 => Report1Export::class,
            2 => Report2Export::class,
            3 => Report3Export::class,
            4 => Report4Export::class,
            5 => Report5Export::class,
            6 => Report6Export::class,
        ];

        $exportClass = $exportClasses[$report] ?? Report1Export::class;

        return Excel::download(new $exportClass($data), $filename);
    }

    /**
     * Shared helper: build the data array for all 6 reports.
     * Used by both reports() and the export methods.
     */
    private function buildReportData(Request $request): array
    {
        $asOf = $request->input('as_of', now()->format('Y-m'));
        $asOfDate = \Carbon\Carbon::parse($asOf . '-01');
        $year  = $asOfDate->year;
        $month = $asOfDate->month;

        $filled = PlantillaRecord::where('is_vacant', false)
            ->whereNull('nature_of_separation')
            ->orderBy('organizational_unit')
            ->orderBy('last_name')
            ->get();

        // Report 1
        $report1 = [];
        foreach ($filled as $r) {
            $office = $r->organizational_unit ?: 'Unassigned';
            $cat    = $this->resolveCategory($r);
            if (!isset($report1[$office])) {
                $report1[$office] = [
                    'Elected' => 0, 'Co-Terminous' => 0, 'Permanent' => 0,
                    'Casual' => 0, 'Job Order' => 0, 'Total' => 0
                ];
            }
            if (array_key_exists($cat, $report1[$office])) {
                $report1[$office][$cat]++;
            }
            $report1[$office]['Total']++;
        }
        ksort($report1);

        // Report 2
        $report2 = $filled->map(function ($r) {
            $age = $r->date_of_birth
                ? \Carbon\Carbon::parse($r->date_of_birth)->age
                : null;
            return [
                'record' => $r,
                'cat'    => $this->resolveCategory($r),
                'age'    => $age,
            ];
        })->groupBy(fn($item) => $item['cat']);

        // Report 3
        $report3 = PlantillaRecord::where('is_vacant', false)
            ->whereNull('nature_of_separation')
            ->whereNotNull('nature_of_appointment')
            ->where(function ($q) use ($year) {
                $q->whereYear('date_original_appointment', $year)
                  ->orWhereYear('date_last_promotion', $year);
            })
            ->orderBy('organizational_unit')
            ->orderBy('last_name')
            ->get()
            ->each(function ($r) {
                $r->resolved_category = $this->resolveCategory($r);
            });

        // Report 4
        $report4 = PlantillaRecord::whereYear('date_separated', $year)
            ->where('nature_of_separation', 'Retired')
            ->orderBy('organizational_unit')
            ->orderBy('last_name')
            ->get()
            ->map(function ($r) {
                $yearsInService = $r->date_original_appointment
                    ? \Carbon\Carbon::parse($r->date_original_appointment)
                        ->diffInYears($r->date_separated ?? now())
                    : null;
                return ['record' => $r, 'years_in_service' => $yearsInService];
            });

        // Report 5
        $report5 = PlantillaRecord::whereYear('date_separated', $year)
            ->whereNotNull('nature_of_separation')
            ->orderBy('organizational_unit')
            ->orderBy('last_name')
            ->get();

        // Report 6
        $report6 = [];
        foreach ($filled as $r) {
            $office = $r->organizational_unit ?: 'Unassigned';
            $cat    = $this->resolveCategory($r);
            if (!isset($report6[$office][$cat])) {
                $report6[$office][$cat] = [];
            }
            $report6[$office][$cat][] = $r;
        }
        ksort($report6);

        return compact('report1', 'report2', 'report3', 'report4', 'report5', 'report6',
                       'asOf', 'year', 'month');
    }


    /**
     * Show form to create a new plantilla record.
     */
    public function create()
    {
        $offices = PlantillaRecord::select('organizational_unit')
            ->distinct()->orderBy('organizational_unit')
            ->pluck('organizational_unit');
        $existingItems = PlantillaRecord::select('item')
            ->distinct()->orderBy('item')
            ->pluck('item')->filter()->values();
        return view('plantilla.create', compact('offices', 'existingItems'));
    }

    /**
     * Store a newly created plantilla record.
     */
    public function store(Request $request)
    {
        $validated = $this->validateRecord($request);
        $validated['is_vacant'] = empty($validated['last_name']) ||
                                  strtoupper(trim($validated['last_name'])) === 'VACANT';

        // Auto-generate employee code if not provided
        if (empty($validated['employee_code']) && !$validated['is_vacant']) {
            $validated['employee_code'] = PlantillaRecord::generateEmployeeCode(
                $validated['last_name'] ?? null,
                $validated['date_of_birth'] ?? null
            );
        }

        $plantilla = PlantillaRecord::create($validated);

        if (Auth::check()) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Created Record',
                'description' => 'Created plantilla record for "' . trim($plantilla->first_name . ' ' . $plantilla->last_name) . '" (Item: ' . $plantilla->item . ')'
            ]);
        }

        return redirect()->route('plantilla.index')
            ->with('success', 'Plantilla record created successfully.');
    }

    /**
     * Display the specified plantilla record.
     */
    public function show(PlantillaRecord $plantilla)
    {
        return view('plantilla.show', compact('plantilla'));
    }

    /**
     * Show the form for editing the plantilla record.
     */
    public function edit(PlantillaRecord $plantilla)
    {
        $offices = PlantillaRecord::select('organizational_unit')
            ->distinct()->orderBy('organizational_unit')
            ->pluck('organizational_unit');
        $existingItems = PlantillaRecord::select('item')
            ->distinct()->orderBy('item')
            ->pluck('item')->filter()->values();
        return view('plantilla.edit', compact('plantilla', 'offices', 'existingItems'));
    }

    /**
     * Update the specified plantilla record.
     */
    public function update(Request $request, PlantillaRecord $plantilla)
    {
        $validated = $this->validateRecord($request, $plantilla->id);
        $validated['is_vacant'] = empty($validated['last_name']) ||
                                  strtoupper(trim($validated['last_name'])) === 'VACANT';

        // Auto-generate employee code if cleared and not vacant
        if (empty($validated['employee_code']) && !$validated['is_vacant']) {
            $validated['employee_code'] = PlantillaRecord::generateEmployeeCode(
                $validated['last_name'] ?? null,
                $validated['date_of_birth'] ?? null
            );
        }

        $plantilla->update($validated);

        if (Auth::check()) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Modified Data',
                'description' => 'Updated plantilla record for "' . trim($plantilla->first_name . ' ' . $plantilla->last_name) . '" (Item: ' . $plantilla->item . ')'
            ]);
        }

        return redirect()->route('plantilla.index')
            ->with('success', 'Plantilla record updated successfully.');
    }

    /**
     * Remove the specified plantilla record.
     */
    public function destroy(PlantillaRecord $plantilla)
    {
        $name = trim($plantilla->first_name . ' ' . $plantilla->last_name);
        $item = $plantilla->item;
        
        $plantilla->delete();

        if (Auth::check()) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Deleted Record',
                'description' => 'Deleted plantilla record for "' . $name . '" (Item: ' . $item . ')'
            ]);
        }

        return redirect()->route('plantilla.index')
            ->with('success', 'Plantilla record deleted successfully.');
    }

    /**
     * Generate Service Record PDF for the employee.
     */
    public function generateServiceRecord(PlantillaRecord $plantilla)
    {
        $data = [
            'employee' => $plantilla,
            // Normally you would fetch a history of appointments.
            // Since this system only stores the current record, we'll
            // display the current appointment as the main entry.
        ];

        $pdf = Pdf::loadView('plantilla.pdf.service-record', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->download('ServiceRecord_' . \Illuminate\Support\Str::slug($plantilla->full_name) . '.pdf');
    }

    /**
     * Generate CSC Form 33 (Appointment Form)
     */
    public function generateForm33(PlantillaRecord $plantilla)
    {
        $pdf = Pdf::loadView('plantilla.pdf.form33', compact('plantilla'))
            ->setPaper('legal', 'portrait');

        return $pdf->download('CSC_Form_33_Appointment_' . \Illuminate\Support\Str::slug($plantilla->position_title) . '.pdf');
    }

    /**
     * Generate CSC Form 9 (Publication of Vacant Positions)
     */
    public function generateForm9()
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(600);

        $vacantRecords = PlantillaRecord::where('is_vacant', true)
                            ->where('abolished', false)
                            ->where('dissolved', false)
                            ->orderBy('organizational_unit')
                            ->orderBy('position_title')
                            ->get();

        // Legal landscape is often used for CSC Form 9 since it has many columns
        $pdf = Pdf::loadView('plantilla.pdf.form9', compact('vacantRecords'))
            ->setPaper('legal', 'landscape');

        return $pdf->download('CSC_Form_9_Vacant_Positions.pdf');
    }

    /**
     * Shared validation rules.
     */
    private function validateRecord(Request $request, ?int $exceptId = null): array
    {
        return $request->validate([
            'organizational_unit'       => 'required|string|max:255',
            'item'                      => 'required|string|max:50',
            'position_title'            => 'required|string|max:255',
            'salary_grade'              => 'required|integer|min:1|max:33',
            'authorized_annual_salary'  => 'nullable|numeric|min:0',
            'actual_annual_salary'      => 'nullable|numeric|min:0',
            'step'                      => 'required|integer|min:1|max:8',
            'area_code'                 => 'nullable|string|max:10',
            'area_type'                 => 'nullable|string|max:5',
            'level'                     => 'nullable|string|max:5',
            'last_name'                 => 'nullable|string|max:100',
            'first_name'                => 'nullable|string|max:100',
            'middle_name'               => 'nullable|string|max:100',
            'sex'                       => 'nullable|in:M,F',
            'date_of_birth'             => 'nullable|date',
            'tin'                       => 'nullable|string|max:50',
            'date_original_appointment' => 'nullable|date',
            'date_last_promotion'       => 'nullable|date',
            'employment_status'         => 'nullable|string|max:20',
            'civil_service_eligibility' => 'nullable|string|max:255',
            'comment_annotation'        => 'nullable|string',
            'is_pwd'                    => 'boolean',
            'indigenous_people'         => 'nullable|string|max:20',
            'solo_parent'               => 'nullable|string|max:100',
            'abolished'                 => 'boolean',
            'dissolved'                 => 'boolean',
            'gsis_bp_number'            => 'nullable|string|max:50',
            'position_classification'   => 'nullable|string|max:100',
            'umid'                      => 'nullable|string|max:50',
            'nature_of_appointment'     => 'nullable|string|max:100',
            'nature_of_separation'      => 'nullable|string|max:100',
            'date_separated'            => 'nullable|date',
            'employee_code'             => 'nullable|string|max:20|unique:plantilla_records,employee_code' . ($exceptId ? ",{$exceptId}" : ''),
        ]);
    }
}
