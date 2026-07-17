<?php

namespace App\Http\Controllers;

use App\Models\PlantillaRecord;
use App\Models\ActivityLog;
use App\Models\DetailOrder;
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
use App\Exports\Reports\Report8Export;

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
        'Elected' => 'bg-purple-100 text-purple-700',
        'Co-Terminous' => 'bg-indigo-100 text-indigo-700',
        'Permanent' => 'bg-green-100 text-green-700',
        'Casual' => 'bg-amber-100 text-amber-700',
        'Job Order' => 'bg-orange-100 text-orange-700',
        'Vacant Funded' => 'bg-blue-100 text-blue-700',
        'Vacant Unfunded' => 'bg-gray-200 text-gray-600',
    ];

    /**
     * Returns true when both annual-salary fields are zero / null.
     * Such positions are treated as Vacant Unfunded automatically.
     */
    private function hasZeroSalary(PlantillaRecord $record): bool
    {
        $s = strtolower(trim($record->employment_status ?? ''));
        if (in_array($s, ['job order', 'jo', 'j.o.', 'job-order', 'casual', 'cas']) || str_contains($s, 'job') || str_contains($s, 'casual')) {
            return false;
        }

        $auth = (float) ($record->authorized_annual_salary ?? 0);
        $actual = (float) ($record->base_salary_amount ?? 0);
        return $auth === 0.0 && $actual === 0.0;
    }

    private function resolveCategory(PlantillaRecord $record): string
    {
        $s = strtolower(trim($record->employment_status ?? ''));

        if (in_array($s, ['elected', 'e']) || str_contains($s, 'elect')) return 'Elected';
        if (in_array($s, ['co-terminous', 'coterminous', 'co terminous', 'ct']) || str_contains($s, 'terminous')) return 'Co-Terminous';
        if (in_array($s, ['casual', 'cas']) || str_contains($s, 'casual')) return 'Casual';
        if (in_array($s, ['job order', 'jo', 'j.o.', 'job-order']) || str_contains($s, 'job')) return 'Job Order';

        // Zero-salary positions are always Vacant Unfunded (Plantilla only)
        if ($this->hasZeroSalary($record)) {
            return 'Vacant Unfunded';
        }

        if ($record->is_vacant) {
            return ($record->abolished || $record->dissolved)
                ? 'Vacant Unfunded'
                : 'Vacant Funded';
        }

        return 'Permanent'; // safe default
    }

    /**
     * Display Inventory of Personnel dashboard + grouped list.
     */
    public function index(Request $request)
    {
        session(['last_index_url' => request()->fullUrl()]);


        // ── 1. STATS: Optimize with DB aggregates & caching ─────────────────────

        $stats = cache()->remember('plantilla_stats_v3', 60, function () {
            $allRecords = PlantillaRecord::select(
                'is_vacant',
                'employment_status',
                'date_of_birth',
                'sex',
                'abolished',
                'dissolved',
                'authorized_annual_salary',
                'base_salary_amount',
                'position_title',
                'office_department',
                'first_name',
                'last_name',
                'middle_name',
                'nature_of_separation'
            )
            ->where(function($q) {
                $q->whereNull('nature_of_separation')->orWhere('is_vacant', true);
            })
            ->get();
            $filledAll = $allRecords->where('is_vacant', false);

            // Status counts and Gender Breakdown per Status
            $statusCounts = array_fill_keys(self::CATEGORIES, 0);
            $genderStats = array_fill_keys(self::CATEGORIES, ['M' => 0, 'F' => 0, 'Unknown' => 0]);
            
            foreach ($allRecords as $r) {
                $cat = $this->resolveCategory($r);
                if (isset($statusCounts[$cat])) {
                    $statusCounts[$cat]++;
                }
            }

            // Age ranges (filled employees with a known DOB)
            $ageRanges = ['21-30' => 0, '31-40' => 0, '41-50' => 0, '51-60' => 0, '61-65' => 0, 'Other' => 0];
            $nearRetirement = [];           // employees aged 61–65
            $genderCounts = ['M' => 0, 'F' => 0, 'Unknown' => 0];

            foreach ($filledAll as $r) {
                $cat = $this->resolveCategory($r);
                $sex = strtoupper(trim($r->sex ?? ''));
                if (!in_array($sex, ['M', 'F'])) $sex = 'Unknown';
                
                $genderCounts[$sex]++;
                if (isset($genderStats[$cat])) {
                    $genderStats[$cat][$sex]++;
                }

                if (empty($r->date_of_birth))
                    continue;
                try {
                    $age = \Carbon\Carbon::parse($r->date_of_birth)->age;
                    if ($age >= 61 && $age <= 65) {
                        $ageRanges['61-65']++;
                        $nearRetirement[] = clone $r;
                    } // Clone to detach from big collection
                    elseif ($age >= 51 && $age <= 60) {
                        $ageRanges['51-60']++;
                    } elseif ($age >= 41 && $age <= 50) {
                        $ageRanges['41-50']++;
                    } elseif ($age >= 31 && $age <= 40) {
                        $ageRanges['31-40']++;
                    } elseif ($age >= 21 && $age <= 30) {
                        $ageRanges['21-30']++;
                    } else {
                        $ageRanges['Other']++;
                    }
                } catch (\Exception) {
                }
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
                ->filter(
                    fn($r) =>
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
                'genderStats' => $genderStats,
                'vacantFunded' => $vacantFunded,
                'vacantUnfunded' => $vacantUnfunded,
                'totalAll' => $allRecords->count()
            ];
        });

        // Extract cached stats
        $statusCounts = $stats['statusCounts'];
        $ageRanges = $stats['ageRanges'];
        $nearRetirement = collect($stats['nearRetirement']); // Convert back to collection for view
        $genderCounts = $stats['genderCounts'];
        $vacantFunded = $stats['vacantFunded'];
        $vacantUnfunded = $stats['vacantUnfunded'];
        $totalAll = $stats['totalAll'];
        $genderStats = $stats['genderStats'];

        // ── 2. FILTERED LIST: per-office accordion ─────────────────────────
        $query = PlantillaRecord::query()
            ->where(function($q) {
                $q->whereNull('nature_of_separation')->orWhere('is_vacant', true);
            })
            ->orderBy('office_department')
            ->orderBy('item_no_new');

        $isFiltered = false;

        if ($request->filled('search')) {
            $isFiltered = true;
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('last_name', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('item_no_new', 'like', "%{$search}%")
                    ->orWhere('position_title', 'like', "%{$search}%");
            });
        }
        if ($request->filled('office')) {
            $isFiltered = true;
            $selectedOffice = $request->input('office');
            $query->where('office_department', $selectedOffice);
        }
        if ($request->filled('age_range')) {
            $isFiltered = true;
            $ageRange = $request->input('age_range');
            $parts = explode('-', $ageRange);
            if (count($parts) === 2) {
                $minAge = (int) $parts[0];
                $maxAge = (int) $parts[1];
                $now = \Carbon\Carbon::now();
                $minDate = $now->copy()->subYears($maxAge + 1)->addDay()->format('Y-m-d');
                $maxDate = $now->copy()->subYears($minAge)->format('Y-m-d');
                $query->whereDate('date_of_birth', '>=', $minDate)
                    ->whereDate('date_of_birth', '<=', $maxDate);
            }
        }
        if ($request->filled('vacancy_status')) {
            $isFiltered = true;
            if ($request->input('vacancy_status') === 'vacant') {
                $query->where('is_vacant', true);
            } elseif ($request->input('vacancy_status') === 'filled') {
                $query->where('is_vacant', false);
            }
        }
        if ($request->filled('category')) {
            $isFiltered = true;
            $cat = $request->input('category');
            if ($cat === 'Vacant Funded') {
                $query->where('is_vacant', true)->where('abolished', false)->where('dissolved', false);
            } elseif ($cat === 'Vacant Unfunded') {
                $query->where(function ($q) {
                    // abolished/dissolved vacants
                    $q->where(function ($q2) {
                        $q2->where('is_vacant', true)
                            ->where(function ($q3) {
                                $q3->where('abolished', true)->orWhere('dissolved', true);
                            });
                    })
                        // OR zero-salary records
                        ->orWhere(function ($q2) {
                            $q2->where(function ($q3) {
                                $q3->whereNull('authorized_annual_salary')
                                    ->orWhere('authorized_annual_salary', 0);
                            })->where(function ($q3) {
                                $q3->whereNull('base_salary_amount')
                                    ->orWhere('base_salary_amount', 0);
                            });
                        });
                });
            } else {
                $statusMap = [
                    'Elected' => ['E', 'Elected'],
                    'Co-Terminous' => ['CT', 'Co-Terminous', 'Coterminous'],
                    'Permanent' => ['P', 'Permanent'],
                    'Casual' => ['Casual', 'Cas'],
                    'Job Order' => ['JO', 'Job Order', 'J.O.'],
                ];
                $query->where('is_vacant', false)->whereIn('employment_status', $statusMap[$cat] ?? [$cat]);
            }
        } else {
            // Default: hide vacant records so only regular filled employees are displayed.
            $query->where('is_vacant', false);
        }
        
        if ($request->filled('sex')) {
            $isFiltered = true;
            $query->where('sex', strtoupper($request->input('sex')));
        }
        if ($request->filled('position')) {
            $isFiltered = true;
            $query->where('position_title', $request->input('position'));
        }

        $records = $query->paginate(50)->withQueryString();

        // Cache offices list since it rarely changes completely
        $offices = cache()->remember('plantilla_offices_list_fixed', 3600, function () {
            return PlantillaRecord::distinct()->pluck('office_department')->filter()->unique()->sort()->values();
        });

        // Distinct position titles for the Position filter dropdown
        $positions = cache()->remember('plantilla_positions_list', 3600, function () {
            return PlantillaRecord::distinct()
                ->orderBy('position_title')
                ->pluck('position_title')
                ->filter()->values();
        });

        // We also need the resolveCategory method available in the view if needed, 
        // but it's protected/private. Let's map it or just rely on simple checks in the view.
        // Actually, we can just attach the resolved category to each record directly.
        $records->getCollection()->transform(function ($record) {
            $record->resolved_category = $this->resolveCategory($record);
            return $record;
        });

        return view('plantilla.index', [
            'records' => $records,
            'offices' => $offices,
            'positions' => $positions,
            'categories' => self::CATEGORIES,
            'catColors' => self::CAT_COLORS,
            'totalAll' => $totalAll,
            // Stats
            'statusCounts' => $statusCounts,
            'ageRanges' => $ageRanges,
            'nearRetirement' => $nearRetirement,
            'genderCounts' => $genderCounts,
            'genderStats' => $genderStats,
            'vacantFunded' => $vacantFunded,
            'vacantUnfunded' => $vacantUnfunded,
            'isFiltered' => $isFiltered, // Pass this to view to show a warning if truncated
            'pwdCount' => PlantillaRecord::where('is_pwd', true)->where('is_vacant', false)->count(),
            'ipCount' => PlantillaRecord::whereNotNull('indigenous_people')->where('indigenous_people', '!=', '')->where('is_vacant', false)->count(),
        ]);
    }

    /**
     * Display the PWD Personnel Report
     */
    public function pwdReport()
    {
        $pwdRecords = PlantillaRecord::where('is_pwd', true)
            ->where('is_vacant', false)
            ->orderBy('office_department')
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
            ->orderBy('office_department')
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
            ->orderBy('office_department')
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
            ->orderBy('office_department')
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
        $records = collect();

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

        $records = PlantillaRecord::whereIn('position_title', $position)
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
        $office = $request->input('office');

        if ($type === 'unfunded') {
            $records = PlantillaRecord::where(function ($q) {
                $q->where(function ($q2) {
                    $q2->where('is_vacant', true)
                        ->where(function ($q3) {
                            $q3->where('abolished', true)->orWhere('dissolved', true); });
                })->orWhere(function ($q2) {
                    $q2->where(function ($q3) {
                        $q3->whereNull('authorized_annual_salary')->orWhere('authorized_annual_salary', 0);
                    })->where(function ($q3) {
                        $q3->whereNull('base_salary_amount')->orWhere('base_salary_amount', 0);
                    });
                });
            })->when($office, fn ($q) => $q->where('office_department', $office))
                ->orderBy('position_title')->get();
            $title = 'Vacant Unfunded Positions';
        } else {
            $records = PlantillaRecord::where('is_vacant', true)
                ->where('abolished', false)->where('dissolved', false)
                ->when($office, fn ($q) => $q->where('office_department', $office))
                ->orderBy('position_title')->get();
            $title = 'Vacant Funded Positions';
        }
        if ($office) {
            $title .= " — {$office}";
        }

        $pdf = Pdf::loadView('exports.vacant-pdf', compact('records', 'title', 'type'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('Vacant_' . ucfirst($type) . '_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export Vacant Positions list as Excel.
     * ?type=funded|unfunded&office=exact office_department name (optional)
     */
    public function exportVacantExcel(Request $request)
    {
        $type = $request->input('type', 'funded');
        $office = $request->input('office');
        $filename = 'Vacant_' . ucfirst($type) . '_' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new \App\Exports\VacantExport($type, $office), $filename);
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
            ->orderBy('office_department')
            ->orderBy('item_no_new')
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
            ->orderBy('office_department')
            ->orderBy('item_no_new')
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

        $records = PlantillaRecord::where(function ($q) {
            // abolished/dissolved vacants
            $q->where(function ($q2) {
                $q2->where('is_vacant', true)
                    ->where(function ($q3) {
                        $q3->where('abolished', true)->orWhere('dissolved', true);
                    });
            })
                // OR zero-salary records
                ->orWhere(function ($q2) {
                    $q2->where(function ($q3) {
                        $q3->whereNull('authorized_annual_salary')
                            ->orWhere('authorized_annual_salary', 0);
                    })->where(function ($q3) {
                        $q3->whereNull('base_salary_amount')
                            ->orWhere('base_salary_amount', 0);
                    });
                });
        })
            ->where('position_title', $position)
            ->orderBy('office_department')
            ->orderBy('item_no_new')
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

        $records = PlantillaRecord::where(function ($q) {
            $q->where(function ($q2) {
                $q2->where('is_vacant', true)
                    ->where(function ($q3) {
                        $q3->where('abolished', true)->orWhere('dissolved', true);
                    });
            })->orWhere(function ($q2) {
                $q2->where(function ($q3) {
                    $q3->whereNull('authorized_annual_salary')
                        ->orWhere('authorized_annual_salary', 0);
                })->where(function ($q3) {
                    $q3->whereNull('base_salary_amount')
                        ->orWhere('base_salary_amount', 0);
                });
            });
        })
            ->where('position_title', $position)
            ->orderBy('office_department')
            ->orderBy('item_no_new')
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
            ->orderBy('office_department')
            ->orderBy('last_name')
            ->get();

        // ── REPORT 1: Inventory by Office × Appointment Type ──────────────────
        // Columns: Office | Permanent | Elected | Co-Terminous | Casual | Job Order | Total
        $report1 = [];
        foreach ($filled as $r) {
            $office = $r->office_department ?: 'Unassigned';
            $cat = $this->resolveCategory($r);
            if (!isset($report1[$office])) {
                $report1[$office] = [
                    'Elected' => 0,
                    'Co-Terminous' => 0,
                    'Permanent' => 0,
                    'Casual' => 0,
                    'Job Order' => 0,
                    'Total' => 0
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
                'record' => $r,
                'cat' => $this->resolveCategory($r),
                'age' => $age,
            ];
        })->groupBy(fn($item) => $item['cat']);

        // ── REPORT 3: Newly Hired / Promoted / Demoted (filtered by year) ──────
        $report3 = PlantillaRecord::where('is_vacant', false)
            ->whereNull('nature_of_separation')
            ->whereNotNull('nature_of_appointment')
            ->where(function ($q) use ($year) {
                $q->whereYear('date_original_appointment', $year)
                    ->orWhereYear('date_last_promotion', $year);
            })
            ->orderBy('office_department')
            ->orderBy('last_name')
            ->get()
            ->each(function ($r) {
                $r->resolved_category = $this->resolveCategory($r);
            });

        // ── REPORT 4: List of Retirees ─────────────────────────────────────────
        $report4 = PlantillaRecord::whereYear('date_separated', $year)
            ->where('nature_of_separation', 'Retired')
            ->orderBy('office_department')
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
            ->orderBy('office_department')
            ->orderBy('last_name')
            ->get();

        // ── REPORT 6: By Employment Status grouped by Office ───────────────────
        // Office | Status | Total | Employee list | Gender
        $report6 = [];
        foreach ($filled as $r) {
            $office = $r->office_department ?: 'Unassigned';
            $cat = $this->resolveCategory($r);
            if (!isset($report6[$office][$cat])) {
                $report6[$office][$cat] = [];
            }
            $report6[$office][$cat][] = $r;
        }
        ksort($report6);

        $offices = PlantillaRecord::distinct()
            ->orderBy('office_department')
            ->pluck('office_department')
            ->filter()->values();

        // ── REPORT 7: Custom Status Report ─────────────────────────────────────────
        $r7_status = $request->input('r7_status', '');
        $r7_period = $request->input('r7_period', 'range');
        $r7_from = $request->input('r7_from');
        $r7_to = $request->input('r7_to');
        $r7_asof = $request->input('r7_asof');

        $report7 = collect();
        if ($r7_status) {
            $q = PlantillaRecord::query();

            // Date filtering
            if ($r7_period === 'range' && $r7_from && $r7_to) {
                if ($r7_status === 'Newly Hired') {
                    $q->whereBetween('date_original_appointment', [$r7_from, $r7_to]);
                } elseif ($r7_status === 'Promoted') {
                    $q->whereBetween('date_last_promotion', [$r7_from, $r7_to]);
                } elseif ($r7_status === 'Retired' || $r7_status === 'Terminated') {
                    $q->whereBetween('date_separated', [$r7_from, $r7_to]);
                }
            } elseif ($r7_period === 'asof' && $r7_asof) {
                if ($r7_status === 'Newly Hired') {
                    $q->where('date_original_appointment', '<=', $r7_asof);
                } elseif ($r7_status === 'Promoted') {
                    $q->where('date_last_promotion', '<=', $r7_asof);
                } elseif ($r7_status === 'Retired' || $r7_status === 'Terminated') {
                    $q->where('date_separated', '<=', $r7_asof);
                }
            }

            // Status filtering
            if ($r7_status === 'Newly Hired') {
                $q->whereNotNull('date_original_appointment');
            } elseif ($r7_status === 'Promoted') {
                $q->whereNotNull('date_last_promotion');
            } elseif ($r7_status === 'Retired') {
                $q->where('nature_of_separation', 'Retired')->whereNotNull('date_separated');
            } elseif ($r7_status === 'Terminated') {
                $q->whereNotNull('nature_of_separation')->where('nature_of_separation', '!=', 'Retired')->whereNotNull('date_separated');
            }

            $report7 = $q->orderBy('office_department')
                ->orderBy('last_name')
                ->get();
        }

        // ── REPORT 8: Detailed Employees (Enhancement Spec Sec. 5) ─────────────
        $r8_include_recalled = $request->boolean('r8_include_recalled');
        $report8 = DetailOrder::with('plantillaRecord')
            ->when(!$r8_include_recalled, fn ($q) => $q->where('status', '!=', 'Recalled'))
            ->get()
            ->sortBy([
                ['detailed_unit', 'asc'],
                [fn ($o) => $o->plantillaRecord?->last_name ?? '', 'asc'],
            ])
            ->values();

        return view('plantilla.reports', compact(
            'report1',
            'report2',
            'report3',
            'report4',
            'report5',
            'report6',
            'report7',
            'report8',
            'asOf',
            'year',
            'month',
            'offices',
            'r7_status',
            'r7_period',
            'r7_from',
            'r7_to',
            'r7_asof',
            'r8_include_recalled'
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
            8 => Report8Export::class,
        ];

        $exportClass = $exportClasses[$report] ?? Report1Export::class;

        return Excel::download(new $exportClass($data), $filename);
    }

    /**
     * Export Custom Report (Report 7) as PDF.
     * Route: GET plantilla/reports/custom/pdf
     */
    public function exportCustomReportPdf(Request $request)
    {
        $r7_status = $request->input('status', '');
        $r7_period = $request->input('period', 'range');
        $r7_from = $request->input('from');
        $r7_to = $request->input('to');
        $r7_asof = $request->input('asof');

        $report7 = collect();
        if ($r7_status) {
            $q = PlantillaRecord::query();

            // Date filtering
            if ($r7_period === 'range' && $r7_from && $r7_to) {
                if ($r7_status === 'Newly Hired') {
                    $q->whereBetween('date_original_appointment', [$r7_from, $r7_to]);
                } elseif ($r7_status === 'Promoted') {
                    $q->whereBetween('date_last_promotion', [$r7_from, $r7_to]);
                } elseif ($r7_status === 'Retired' || $r7_status === 'Terminated') {
                    $q->whereBetween('date_separated', [$r7_from, $r7_to]);
                }
            } elseif ($r7_period === 'asof' && $r7_asof) {
                if ($r7_status === 'Newly Hired') {
                    $q->where('date_original_appointment', '<=', $r7_asof);
                } elseif ($r7_status === 'Promoted') {
                    $q->where('date_last_promotion', '<=', $r7_asof);
                } elseif ($r7_status === 'Retired' || $r7_status === 'Terminated') {
                    $q->where('date_separated', '<=', $r7_asof);
                }
            }

            // Status filtering
            if ($r7_status === 'Newly Hired') {
                $q->whereNotNull('date_original_appointment');
            } elseif ($r7_status === 'Promoted') {
                $q->whereNotNull('date_last_promotion');
            } elseif ($r7_status === 'Retired') {
                $q->where('nature_of_separation', 'Retired')->whereNotNull('date_separated');
            } elseif ($r7_status === 'Terminated') {
                $q->whereNotNull('nature_of_separation')->where('nature_of_separation', '!=', 'Retired')->whereNotNull('date_separated');
            }

            $report7 = $q->orderBy('office_department')
                ->orderBy('last_name')
                ->get();
        }

        $data = compact('report7', 'r7_status', 'r7_period', 'r7_from', 'r7_to', 'r7_asof');

        $filename = "CustomReport_" . str_replace(' ', '', $r7_status) . ".pdf";

        $pdf = Pdf::loadView('exports.reports.report7-pdf', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

    /**
     * Export Custom Report (Report 7) as Excel.
     * Route: GET plantilla/reports/custom/excel
     */
    public function exportCustomReportExcel(Request $request)
    {
        $r7_status = $request->input('status', '');
        $r7_period = $request->input('period', 'range');
        $r7_from = $request->input('from');
        $r7_to = $request->input('to');
        $r7_asof = $request->input('asof');

        $report7 = collect();
        if ($r7_status) {
            $q = PlantillaRecord::query();

            // Date filtering
            if ($r7_period === 'range' && $r7_from && $r7_to) {
                if ($r7_status === 'Newly Hired') {
                    $q->whereBetween('date_original_appointment', [$r7_from, $r7_to]);
                } elseif ($r7_status === 'Promoted') {
                    $q->whereBetween('date_last_promotion', [$r7_from, $r7_to]);
                } elseif ($r7_status === 'Retired' || $r7_status === 'Terminated') {
                    $q->whereBetween('date_separated', [$r7_from, $r7_to]);
                }
            } elseif ($r7_period === 'asof' && $r7_asof) {
                if ($r7_status === 'Newly Hired') {
                    $q->where('date_original_appointment', '<=', $r7_asof);
                } elseif ($r7_status === 'Promoted') {
                    $q->where('date_last_promotion', '<=', $r7_asof);
                } elseif ($r7_status === 'Retired' || $r7_status === 'Terminated') {
                    $q->where('date_separated', '<=', $r7_asof);
                }
            }

            // Status filtering
            if ($r7_status === 'Newly Hired') {
                $q->whereNotNull('date_original_appointment');
            } elseif ($r7_status === 'Promoted') {
                $q->whereNotNull('date_last_promotion');
            } elseif ($r7_status === 'Retired') {
                $q->where('nature_of_separation', 'Retired')->whereNotNull('date_separated');
            } elseif ($r7_status === 'Terminated') {
                $q->whereNotNull('nature_of_separation')->where('nature_of_separation', '!=', 'Retired')->whereNotNull('date_separated');
            }

            $report7 = $q->orderBy('office_department')
                ->orderBy('last_name')
                ->get();
        }

        $data = compact('report7', 'r7_status', 'r7_period', 'r7_from', 'r7_to', 'r7_asof');

        $filename = "CustomReport_" . str_replace(' ', '', $r7_status) . ".xlsx";

        return Excel::download(new \App\Exports\Reports\Report7Export($data), $filename);
    }

    /**
     * Shared helper: build the data array for all 6 reports.
     * Used by both reports() and the export methods.
     */
    private function buildReportData(Request $request): array
    {
        $asOf = $request->input('as_of', now()->format('Y-m'));
        $asOfDate = \Carbon\Carbon::parse($asOf . '-01');
        $year = $asOfDate->year;
        $month = $asOfDate->month;

        $filled = PlantillaRecord::where('is_vacant', false)
            ->whereNull('nature_of_separation')
            ->orderBy('office_department')
            ->orderBy('last_name')
            ->get();

        // Report 1
        $report1 = [];
        foreach ($filled as $r) {
            $office = $r->office_department ?: 'Unassigned';
            $cat = $this->resolveCategory($r);
            if (!isset($report1[$office])) {
                $report1[$office] = [
                    'Elected' => 0,
                    'Co-Terminous' => 0,
                    'Permanent' => 0,
                    'Casual' => 0,
                    'Job Order' => 0,
                    'Total' => 0
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
                'cat' => $this->resolveCategory($r),
                'age' => $age,
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
            ->orderBy('office_department')
            ->orderBy('last_name')
            ->get()
            ->each(function ($r) {
                $r->resolved_category = $this->resolveCategory($r);
            });

        // Report 4
        $report4 = PlantillaRecord::whereYear('date_separated', $year)
            ->where('nature_of_separation', 'Retired')
            ->orderBy('office_department')
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
            ->orderBy('office_department')
            ->orderBy('last_name')
            ->get();

        // Report 6
        $report6 = [];
        foreach ($filled as $r) {
            $office = $r->office_department ?: 'Unassigned';
            $cat = $this->resolveCategory($r);
            if (!isset($report6[$office][$cat])) {
                $report6[$office][$cat] = [];
            }
            $report6[$office][$cat][] = $r;
        }
        ksort($report6);

        // Report 8
        $r8_include_recalled = $request->boolean('r8_include_recalled');
        $report8 = DetailOrder::with('plantillaRecord')
            ->when(!$r8_include_recalled, fn ($q) => $q->where('status', '!=', 'Recalled'))
            ->get()
            ->sortBy([
                ['detailed_unit', 'asc'],
                [fn ($o) => $o->plantillaRecord?->last_name ?? '', 'asc'],
            ])
            ->values();

        return compact(
            'report1',
            'report2',
            'report3',
            'report4',
            'report5',
            'report6',
            'report8',
            'asOf',
            'year',
            'month'
        );
    }


    /**
     * Get the details of a specific Item (Position Code).
     * Route: GET plantilla/item-details
     */
    public function getItemDetails(Request $request)
    {
        $item = $request->query('item_no_new');
        if (!$item) {
            return response()->json([]);
        }

        $record = PlantillaRecord::where('item_no_new', $item)->first();
        if ($record) {
            return response()->json($record);
        }

        return response()->json([]);
    }

    /**
     * Show form to create a new plantilla record.
     */
    public function create()
    {
        $offices = PlantillaRecord::select('office_department')
            ->distinct()->orderBy('office_department')
            ->pluck('office_department')->filter()->values();
        $positions = PlantillaRecord::select('position_title')
            ->distinct()->orderBy('position_title')
            ->pluck('position_title')->filter()->values();
        $existingItems = PlantillaRecord::select('item_no_new')
            ->distinct()->orderBy('item_no_new')
            ->pluck('item_no_new')->filter()->values();
        return view('plantilla.create', compact('offices', 'existingItems', 'positions'));
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
                $validated['first_name']    ?? null,
                $validated['date_of_birth'] ?? null,
                null,
                $validated['middle_name']   ?? null,
                $validated['last_name']     ?? null
            );
        }

        $plantilla = PlantillaRecord::create($validated);

        if (Auth::check()) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Created Record',
                'description' => 'Created plantilla record for "' . trim($plantilla->first_name . ' ' . $plantilla->last_name) . '" (Item: ' . $plantilla->item_no_new . ')'
            ]);
        }

        return redirect(session('last_index_url', route('plantilla.index')))
            ->with('success', 'Plantilla record created successfully.');
    }

    /**
     * Display the specified plantilla record.
     */
    public function show(PlantillaRecord $plantilla)
    {
        // Enhancement Spec Sec. 4 — read-only 201-file feed: the immutable
        // ledger joined with its append-only status history at read-time.
        $violations = \App\Models\Violation::where('plantilla_record_id', $plantilla->id)
            ->with('statusLogs')
            ->orderByDesc('date_created')
            ->get();

        // Attach the generated notice/letter document (if any) for Leave-sourced
        // violations, so the 201-file view can link straight to the actual letter —
        // previously the ledger only showed the violation type/status, not the letter itself.
        $leaveViolationIds = $violations->where('source_module', 'Leave')->pluck('source_record_id');
        $leaveViolationsById = \App\Models\LeaveViolation::whereIn('id', $leaveViolationIds)
            ->with('document')
            ->get()
            ->keyBy('id');
        $violations->each(function ($v) use ($leaveViolationsById) {
            $v->letterDocument = $v->source_module === 'Leave'
                ? $leaveViolationsById->get($v->source_record_id)?->document
                : null;
        });

        return view('plantilla.show', compact('plantilla', 'violations'));
    }

    /**
     * Show the form for editing the plantilla record.
     */
    public function edit(PlantillaRecord $plantilla)
    {
        $offices = PlantillaRecord::select('office_department')
            ->distinct()->orderBy('office_department')
            ->pluck('office_department')->filter()->values();
        $positions = PlantillaRecord::select('position_title')
            ->distinct()->orderBy('position_title')
            ->pluck('position_title')->filter()->values();
        $existingItems = PlantillaRecord::select('item_no_new')
            ->distinct()->orderBy('item_no_new')
            ->pluck('item_no_new')->filter()->values();
        // Enhancement Spec Sec. 6 — candidates for "Originating Disciplinary Case"
        // on Termination; this employee's own cases plus any still open.
        $disciplinaryCases = \App\Models\DisciplinaryCase::where('personnel_id', $plantilla->id)
            ->orWhere('status', '!=', 'closed')
            ->orderByDesc('id')
            ->get();
        return view('plantilla.edit', compact('plantilla', 'offices', 'existingItems', 'positions', 'disciplinaryCases'));
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
                $validated['first_name']    ?? null,
                $validated['date_of_birth'] ?? null,
                null,
                $validated['middle_name']   ?? null,
                $validated['last_name']     ?? null
            );
        }

        // Auto-Process Separation/Retirement if requested
        if ($request->boolean('process_separation')) {
            $sepType = $request->input('nature_of_separation', 'SEPARATED');
            $sepDate = $request->input('date_separated', now()->toDateString());
            
            $formerName = trim(
                strtoupper($validated['last_name'] ?? $plantilla->last_name ?? '') . ', ' .
                ($validated['first_name'] ?? $plantilla->first_name ?? '') . ' ' .
                ($validated['middle_name'] ?? $plantilla->middle_name ?? '')
            );
            
            $dobStr = !empty($validated['date_of_birth']) ? \Carbon\Carbon::parse($validated['date_of_birth'])->format('m/d/Y') : 'N/A';
            $sgStr = $validated['salary_grade'] ?? $plantilla->salary_grade ?? '?';
            $stepStr = $validated['step'] ?? $plantilla->step ?? '?';
            $tinStr = $validated['tin'] ?? $plantilla->tin ?? 'N/A';

            $annotation = "{$sepType} effective {$sepDate}. "
                        . "Former employee: {$formerName}. "
                        . "DOB: {$dobStr}. SG-{$sgStr} Step {$stepStr}. TIN: {$tinStr}.";

            $existing = $validated['remarks_annotation'] ?? $plantilla->remarks_annotation;
            $validated['remarks_annotation'] = $existing ? $existing . "\n\n" . $annotation : $annotation;

            // Clear employee fields to auto-declare vacant
            $validated['last_name'] = null;
            $validated['first_name'] = null;
            $validated['middle_name'] = null;
            $validated['sex'] = null;
            $validated['date_of_birth'] = null;
            $validated['tin'] = null;
            $validated['gsis_bp_number'] = null;
            $validated['umid'] = null;
            $validated['employee_code'] = null;
            $validated['is_vacant'] = true;
            
            // Set separation columns
            $validated['nature_of_separation'] = $sepType;
            $validated['date_separated'] = $sepDate;
            
            // If it's retirement, also set retired_at for the Retirement history
            if (stripos($sepType, 'retire') !== false) {
                $validated['retired_at'] = $sepDate;
            }
        }

        $plantilla->update($validated);

        if (Auth::check()) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Modified Data',
                'description' => 'Updated plantilla record for "' . trim($plantilla->first_name . ' ' . $plantilla->last_name) . '" (Item: ' . $plantilla->item_no_new . ')'
            ]);
        }

        $returnUrl = session('last_index_url', route('plantilla.index'));
        return redirect($returnUrl)->with('success', 'Plantilla record updated successfully.');
    }

    /**
     * Remove the specified plantilla record.
     */
    public function destroy(PlantillaRecord $plantilla)
    {
        $name = trim($plantilla->first_name . ' ' . $plantilla->last_name);
        $item = $plantilla->item_no_new;

        $plantilla->delete();

        if (Auth::check()) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Deleted Record',
                'description' => 'Deleted plantilla record for "' . $name . '" (Item: ' . $item . ')'
            ]);
        }

        return redirect(session('last_index_url', route('plantilla.index')))
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
     * Generate CSC Form 9 (Publication of Vacant Positions).
     * ?office=exact office_department name — omit for all offices (default, unchanged from before).
     */
    public function generateForm9(Request $request)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(600);

        $office = $request->input('office');

        $vacantRecords = PlantillaRecord::where('is_vacant', true)
            ->where('abolished', false)
            ->where('dissolved', false)
            ->when($office, fn ($q) => $q->where('office_department', $office))
            ->orderBy('office_department')
            ->orderBy('position_title')
            ->get();

        // Legal landscape is often used for CSC Form 9 since it has many columns
        $pdf = Pdf::loadView('plantilla.pdf.form9', compact('vacantRecords', 'office'))
            ->setPaper('legal', 'landscape');

        $filename = $office
            ? 'CSC_Form_9_Vacant_Positions_' . \Illuminate\Support\Str::slug($office) . '.pdf'
            : 'CSC_Form_9_Vacant_Positions.pdf';

        return $pdf->download($filename);
    }

    /**
     * Show form to promote an employee to a vacant position.
     */
    public function promoteForm(PlantillaRecord $plantilla)
    {
        if ($plantilla->is_vacant) {
            return redirect()->route('plantilla.show', $plantilla->id)
                ->with('error', 'Cannot promote a vacant position.');
        }

        // Get all vacant funded positions grouped by office
        $vacantPositions = PlantillaRecord::where('is_vacant', true)
            ->where('abolished', false)
            ->where('dissolved', false)
            ->orderBy('office_department')
            ->orderBy('position_title')
            ->get()
            ->groupBy('office_department');

        return view('plantilla.promote', compact('plantilla', 'vacantPositions'));
    }

    /**
     * Submit promotion: moves employee details to target position, vacates current.
     */
    public function promoteSubmit(Request $request, PlantillaRecord $plantilla)
    {
        if ($plantilla->is_vacant) {
            return redirect()->route('plantilla.show', $plantilla->id)
                ->with('error', 'Cannot promote a vacant position.');
        }

        $request->validate([
            'target_position_id' => 'required|exists:plantilla_records,id',
            'effective_date' => 'required|date',
        ]);

        $target = PlantillaRecord::findOrFail($request->target_position_id);

        if (!$target->is_vacant) {
            return back()->with('error', 'The selected target position is not vacant.');
        }

        // 1. Copy employee details to target position
        $target->fill([
            'last_name' => $plantilla->last_name,
            'first_name' => $plantilla->first_name,
            'middle_name' => $plantilla->middle_name,
            'sex' => $plantilla->sex,
            'religion' => $plantilla->religion,
            'date_of_birth' => $plantilla->date_of_birth,
            'tin' => $plantilla->tin,
            'date_original_appointment' => $plantilla->date_original_appointment,
            'date_last_promotion' => $request->effective_date, // set promotion date
            'date_last_nolp' => $plantilla->date_last_nolp,
            'employment_status' => $plantilla->employment_status,
            'civil_service_eligibility' => $plantilla->civil_service_eligibility,
            'remarks_annotation' => $plantilla->remarks_annotation,
            'is_pwd' => $plantilla->is_pwd,
            'type_of_disability' => $plantilla->type_of_disability,
            'indigenous_people' => $plantilla->indigenous_people,
            'solo_parent' => $plantilla->solo_parent,
            'gsis_bp_number' => $plantilla->gsis_bp_number,
            'umid' => $plantilla->umid,
            'employee_code' => $plantilla->employee_code,

            // Set as no longer vacant
            'is_vacant' => false,
            'nature_of_appointment' => 'Promoted',
            'nature_of_separation' => null,
            'date_separated' => null,
        ]);
        $target->save();

        // 2. Clear current position
        $oldName = trim($plantilla->first_name . ' ' . $plantilla->last_name);
        $plantilla->fill([
            'last_name' => null,
            'first_name' => null,
            'middle_name' => null,
            'sex' => null,
            'religion' => null,
            'date_of_birth' => null,
            'tin' => null,
            'date_original_appointment' => null,
            'date_last_promotion' => null,
            'date_last_nolp' => null,
            'employment_status' => null,
            'civil_service_eligibility' => null,
            'remarks_annotation' => null,
            'is_pwd' => false,
            'type_of_disability' => null,
            'indigenous_people' => null,
            'solo_parent' => null,
            'gsis_bp_number' => null,
            'umid' => null,
            'employee_code' => null,
            'nature_of_appointment' => null,
            'nature_of_separation' => null,
            'date_separated' => null,

            // Set as vacant
            'is_vacant' => true,
        ]);
        $plantilla->save();

        if (Auth::check()) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Promoted/Transferred Employee',
                'description' => "Promoted {$oldName} from Item {$plantilla->item_no_new} to Item {$target->item_no_new}"
            ]);
        }

        return redirect()->route('plantilla.show', $target->id)
            ->with('success', 'Employee successfully promoted and old position vacated.');
    }

    /**
     * Show quick add form for Office/Position.
     */
    public function quickAddForm()
    {
        $offices = PlantillaRecord::select('office_department')
            ->distinct()->orderBy('office_department')
            ->pluck('office_department');

        return view('plantilla.quick-add', compact('offices'));
    }

    /**
     * Submit quick add for Office/Position.
     * This creates a vacant, unfunded record in the system.
     */
    public function quickAddSubmit(Request $request)
    {
        $validated = $request->validate([
            'office_department' => 'required|string|max:255',
            'item_no_new' => 'required|string|max:50',
            'position_title' => 'required|string|max:255',
            'salary_grade' => 'required|integer|min:1|max:33',
            'step' => 'required|integer|min:1|max:8',
        ]);

        // Default properties for a vacant position
        $validated['is_vacant'] = true;
        $validated['authorized_annual_salary'] = 0;
        $validated['base_salary_amount'] = 0;

        $plantilla = PlantillaRecord::create($validated);

        if (Auth::check()) {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'Created Position/Office',
                'description' => 'Quick added vacant position "' . $plantilla->position_title . '" (Item: ' . $plantilla->item_no_new . ') in ' . $plantilla->office_department
            ]);
        }

        return redirect(session('last_index_url', route('plantilla.index')))
            ->with('success', 'New Office/Position slot successfully created.');
    }

    /**
     * Shared validation rules.
     */
    private function validateRecord(Request $request, ?int $exceptId = null): array
    {
        return $request->validate([
            'office_department' => 'required|string|max:255',
            'item_no_new' => 'required|string|max:50',
            'position_title' => 'required|string|max:255',
            'salary_grade' => 'required|integer|min:1|max:33',
            'authorized_annual_salary' => 'nullable|numeric|min:0',
            'base_salary_amount' => 'nullable|numeric|min:0',
            'step' => 'required|integer|min:1|max:8',
            'area_code' => 'nullable|string|max:10',
            'area_type' => 'nullable|string|max:5',
            'level' => 'nullable|string|max:5',
            'last_name' => 'nullable|string|max:100',
            'first_name' => 'nullable|string|max:100',
            'middle_name'    => 'nullable|string|max:100',
            'name_extension' => 'nullable|string|max:20',
            'sex'            => 'nullable|in:M,F',
            'civil_status'   => 'nullable|string|max:50',
            'date_of_birth'  => 'nullable|date',
            'tin' => 'nullable|string|max:50',
            'date_original_appointment' => 'nullable|date',
            'date_last_promotion' => 'nullable|date',
            'employment_status' => 'nullable|string|max:20',
            'civil_service_eligibility' => 'nullable|string|max:255',
            'remarks_annotation' => 'nullable|string',
            'is_pwd' => 'boolean',
            'indigenous_people' => 'nullable|string|max:20',
            'solo_parent' => 'nullable|string|max:100',
            'abolished' => 'boolean',
            'dissolved' => 'boolean',
            'gsis_bp_number' => 'nullable|string|max:50',
            'position_classification' => 'nullable|string|max:100',
            'umid' => 'nullable|string|max:50',
            'nature_of_appointment' => 'nullable|string|max:100',
            'nature_of_separation' => 'nullable|string|max:100',
            'date_separated' => 'nullable|date',
            'basis_reference' => 'nullable|string|max:255',
            'originating_admin_case_id' => 'nullable|exists:disciplinary_cases,id',
            'employee_code' => 'nullable|string|max:20|unique:plantilla_records,employee_code' . ($exceptId ? ",{$exceptId}" : ''),
        ]);
    }

    /**
     * Module 1.2/1A.6 — the "individual clearance" the batch-renewal block
     * message points to. This is a single-record call into the same shared
     * commit service batch renewal uses; it's what actually flips
     * is_renewed false→true for a flagged record. Also RBAC-gated to
     * renewal_commit authority — same rule, same service, individual scope.
     */
    public function renewIndividual(Request $request, PlantillaRecord $plantilla)
    {
        \App\Support\Renewal\RenewalAuthority::assertCanCommit($request->user());

        $data = $request->validate([
            'contract_start_date' => 'required|date',
            'contract_end_date' => 'required|date|after:contract_start_date',
            'rate' => 'nullable|numeric|min:0',
            'rate_type' => 'nullable|in:Daily,Monthly,Annual',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            app(\App\Support\Renewal\RenewalCommitService::class)->commitOne(
                $plantilla,
                $data['contract_start_date'],
                $data['contract_end_date'],
                $data['rate'] ?? null,
                $data['rate_type'] ?? null,
                $request->user(),
                \App\Http\Controllers\BatchRenewalController::currentRatingPeriod(),
                $data['notes'] ?? null,
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Renewal cleared for {$plantilla->last_name}, {$plantilla->first_name}.");
    }

    public function lbpForm3Options(Request $request)
    {
        $offices = PlantillaRecord::select('office_department')
            ->whereNotNull('office_department')
            ->where('office_department', '!=', '')
            ->distinct()
            ->orderBy('office_department')
            ->pluck('office_department');

        return view('plantilla.lbp-form-3-options', compact('offices'));
    }

    public function exportLbpForm3Excel(Request $request)
    {
        $request->validate([
            'employment_type' => 'required|in:Permanent,Casual',
            'office' => 'nullable|string',
            'year' => 'required|numeric',
        ]);

        $employmentType = $request->input('employment_type');
        $office = $request->input('office');
        $year = $request->input('year');
        $preparedBy = $request->input('prepared_by') ?: 'AIDA B. LOVERES';
        $reviewedBy = $request->input('reviewed_by') ?: 'MAYFE P. ALERTA';
        $approvedBy = $request->input('approved_by') ?: 'ROGELIO NEIL P. ROQUE';
        $currentTranche = $request->input('current_tranche') ?: 'LBC #165<br>2nd Tranche<br>Amount';
        $proposedTranche = $request->input('proposed_tranche') ?: 'EO #64<br>3rd Tranche<br>Amount';
        
        $filename = "LBP_FORM_3_" . str_replace(' ', '_', $employmentType) . "_{$year}_" . now()->format('YmdHis') . ".xlsx";

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\LbpForm3Export($employmentType, $office, $year, $preparedBy, $reviewedBy, $approvedBy, $currentTranche, $proposedTranche),
            $filename
        );
    }

    public function exportLbpForm3Pdf(Request $request)
    {
        ini_set('memory_limit', '2048M');
        set_time_limit(600);

        $request->validate([
            'employment_type' => 'required|in:Permanent,Casual',
            'office' => 'nullable|string',
            'year' => 'required|numeric',
        ]);

        $employmentType = $request->input('employment_type');
        $office = $request->input('office');
        $year = $request->input('year');
        $preparedBy = $request->input('prepared_by') ?: 'AIDA B. LOVERES';
        $reviewedBy = $request->input('reviewed_by') ?: 'MAYFE P. ALERTA';
        $approvedBy = $request->input('approved_by') ?: 'ROGELIO NEIL P. ROQUE';
        $currentTranche = $request->input('current_tranche') ?: 'LBC #165<br>2nd Tranche<br>Amount';
        $proposedTranche = $request->input('proposed_tranche') ?: 'EO #64<br>3rd Tranche<br>Amount';

        $query = PlantillaRecord::query()->where('abolished', false);

        if ($employmentType === 'Permanent') {
            $query->whereIn('employment_status', ['P', 'Permanent']);
        } elseif ($employmentType === 'Casual') {
            $query->whereIn('employment_status', ['Casual', 'CASUAL', 'C']);
        }

        if (!empty($office)) {
            $query->where('office_department', $office);
        }

        $records = $query->orderBy('office_department')
            ->orderBy('item_no_new')
            ->get();

        $groupedRecords = $records->groupBy('office_department');

        $pdf = Pdf::loadView('plantilla.exports.lbp-form-3', compact(
            'groupedRecords', 'employmentType', 'office', 'year', 'preparedBy', 'reviewedBy', 'approvedBy', 'currentTranche', 'proposedTranche'
        ))->setPaper([0, 0, 612, 936], 'portrait');

        $filename = "LBP_FORM_3_" . str_replace(' ', '_', $employmentType) . "_{$year}_" . now()->format('YmdHis') . ".pdf";
        return $pdf->download($filename);
    }
}
