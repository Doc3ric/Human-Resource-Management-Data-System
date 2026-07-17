<?php

namespace App\Support;

use App\Models\Applicant;
use App\Models\ExamSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Module 5.4 — examination routing engine. Classifies applicants within a
 * trailing window (default 9 months) into PGB JO / External / Exempted
 * Special Positions (2025 ORAOHRA), then clusters non-exempt applicants into
 * tables of a configurable max size (default 6) for Date/Time/Room assignment.
 */
class ExamRoutingService
{
    public const DEFAULT_WINDOW_MONTHS = 9;
    public const DEFAULT_TABLE_SIZE = 6;

    public function classify(Applicant $applicant): string
    {
        if ($applicant->is_exam_exempt) {
            return 'exempt';
        }

        if ($applicant->is_pgb_employee && stripos((string) $applicant->pgb_status, 'Job Order') !== false) {
            return 'pgb_jo';
        }

        return 'external';
    }

    /** Applicants within the trailing window, grouped by classification. */
    public function windowedApplicants(int $windowMonths = self::DEFAULT_WINDOW_MONTHS): Collection
    {
        return $this->applicantsInRange(now()->subMonths($windowMonths), null);
    }

    /** Applicants within an explicit application-date range, grouped by classification. */
    public function applicantsInRange(?Carbon $dateFrom, ?Carbon $dateTo, array $filters = []): Collection
    {
        $query = Applicant::where(function ($q) use ($dateFrom, $dateTo) {
                $q->where(function ($q2) use ($dateFrom, $dateTo) {
                    $q2->whereNotNull('applied_at');
                    if ($dateFrom) $q2->where('applied_at', '>=', $dateFrom);
                    if ($dateTo) $q2->where('applied_at', '<=', $dateTo);
                })
                ->orWhere(function ($q2) use ($dateFrom, $dateTo) {
                    $q2->whereNull('applied_at');
                    if ($dateFrom) $q2->where('created_at', '>=', $dateFrom);
                    if ($dateTo) $q2->where('created_at', '<=', $dateTo);
                });
            });

        if (!empty($filters['salary_grade'])) {
            $query->where('salary_grade_snapshot', $filters['salary_grade']);
        }
        
        $grouped = $query->orderBy('created_at')
            ->get()
            ->unique(function ($a) {
                return strtoupper(trim($a->last_name)) . '_' . strtoupper(trim($a->first_name));
            })
            ->groupBy(fn (Applicant $a) => $this->classify($a));

        if (!empty($filters['classification'])) {
            $class = $filters['classification'];
            return collect([$class => $grouped->get($class, collect())]);
        }

        return $grouped;
    }

    /** Cluster a set of (non-exempt) applicants into tables of $tableSize. */
    public function cluster(Collection $applicants, int $tableSize = self::DEFAULT_TABLE_SIZE): Collection
    {
        return $applicants->values()->chunk(max(1, $tableSize));
    }

    /**
     * Persist the clustering as exam_schedules rows (Date/Time/Room supplied
     * by the examiner before generation). Re-generating for an applicant
     * replaces their prior schedule row rather than creating a duplicate.
     */
    public function generate(Collection $tables, string $classification, ?string $examDate, ?string $examTime, ?string $room, User $user): void
    {
        foreach ($tables as $tableIndex => $table) {
            foreach ($table as $applicant) {
                ExamSchedule::updateOrCreate(
                    ['applicant_id' => $applicant->id],
                    [
                        'classification' => $classification,
                        'table_number' => $tableIndex + 1,
                        'exam_date' => $examDate,
                        'exam_time' => $examTime,
                        'room' => $room,
                        'generated_by' => $user->id,
                    ]
                );
            }
        }
    }
}
