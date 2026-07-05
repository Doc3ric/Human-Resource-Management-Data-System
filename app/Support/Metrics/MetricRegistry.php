<?php

namespace App\Support\Metrics;

use App\Models\IncidentReport;
use App\Models\LeaveViolation;
use App\Models\PlantillaRecord;
use App\Models\ServiceRequest;
use App\Models\Training;
use App\Models\User;

/**
 * Module 12.3/12.8 — the Metric Ownership Registry. Every statistic has
 * exactly one canonical owner; anywhere else that needs the number renders
 * a reference tile pointing here rather than recomputing it. This is a
 * starting registry covering the metrics this session actually built real
 * data for — NOT a full audit of every pre-existing count in the app (see
 * [[m12-scoreboard]] for what a full 12.7 consolidation would still need
 * to cover, e.g. DashboardController's dozens of ad-hoc counts).
 */
class MetricRegistry
{
    /** @return array{key: string, label: string, value: mixed, owner: string, owner_route: string}|null */
    public static function get(string $key, User $user): ?array
    {
        return match ($key) {
            'renewal_active_not_renewed' => self::renewalActiveNotRenewed(),
            'leave_violation_counts' => self::leaveViolationCounts(),
            'incident_counts_by_track' => self::incidentCountsByTrack(),
            'training_hours_delivered' => self::trainingHoursDelivered(),
            'lgu_service_requests_overdue' => self::lguServiceRequestsOverdue(),
            'workforce_headcount_by_sex' => self::workforceHeadcountBySex(),
            'workforce_pwd_count' => self::workforcePwdCount(),
            'workforce_solo_parent_count' => self::workforceSoloParentCount(),
            default => null,
        };
    }

    private static function renewalActiveNotRenewed(): array
    {
        $notRenewed = PlantillaRecord::whereIn('employment_status', PlantillaRecord::RENEWAL_GATED_STATUSES)
            ->where('is_renewed', false)->where('is_vacant', false)->count();
        $renewed = PlantillaRecord::whereIn('employment_status', PlantillaRecord::RENEWAL_GATED_STATUSES)
            ->where('is_renewed', true)->where('is_vacant', false)->count();

        return [
            'key' => 'renewal_active_not_renewed',
            'label' => 'Not Renewed (Active Casual/JO)',
            'value' => $notRenewed,
            'context' => "{$renewed} renewed / {$notRenewed} not renewed",
            'owner' => 'Renewal & Active Status (1A)',
            'owner_route' => 'batch-renewal.index',
        ];
    }

    private static function leaveViolationCounts(): array
    {
        $counts = LeaveViolation::selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status');

        return [
            'key' => 'leave_violation_counts',
            'label' => 'Leave Violations (by status)',
            'value' => $counts->sum(),
            'context' => $counts->map(fn ($c, $s) => "{$s}: {$c}")->implode(', ') ?: 'No violations this period — all clear.',
            'owner' => 'Leave Violation Monitoring (2B)',
            'owner_route' => 'leave-violations.index',
        ];
    }

    private static function incidentCountsByTrack(): array
    {
        $counts = IncidentReport::whereNotNull('final_track')
            ->selectRaw('final_track, count(*) as c')->groupBy('final_track')->pluck('c', 'final_track');

        return [
            'key' => 'incident_counts_by_track',
            'label' => 'Incidents (counseling vs escalated)',
            'value' => $counts->sum(),
            'context' => $counts->map(fn ($c, $t) => "{$t}: {$c}")->implode(', ') ?: 'No finalized incidents this period.',
            'owner' => 'Incident Report (3A)',
            'owner_route' => 'incidents.index',
        ];
    }

    private static function trainingHoursDelivered(): array
    {
        $hours = Training::sum('total_hours');
        $count = Training::count();

        return [
            'key' => 'training_hours_delivered',
            'label' => 'Training Hours Delivered',
            'value' => (float) $hours,
            'context' => "{$count} training(s) recorded",
            'owner' => 'Learning & Development (10B)',
            'owner_route' => 'training.index',
        ];
    }

    private static function lguServiceRequestsOverdue(): array
    {
        $overdue = ServiceRequest::where('status', 'pending')->where('due_at', '<', now())->count();

        return [
            'key' => 'lgu_service_requests_overdue',
            'label' => "Overdue Citizen's Charter Requests",
            'value' => $overdue,
            'context' => $overdue > 0 ? "{$overdue} request(s) past the Citizen's Charter window" : 'No overdue requests — all clear.',
            'owner' => 'LGU Documents (M5)',
            'owner_route' => 'lgu.index',
        ];
    }

    /**
     * Module 12.3 canonical owner: GAD & Workforce / Analytics. Computed via
     * PlantillaRecord::filled() (the rule-compliant scope — excludes
     * abolished/separated/unrenewed personnel per Module 1/1B), the same
     * figure GadAnalyticsController's own page currently computes with a
     * looser, non-gated base query — a real divergence this registry entry
     * is the single corrected source for, not a duplicate of GAD's number.
     */
    private static function workforceHeadcountBySex(): array
    {
        $male = PlantillaRecord::filled()->where('sex', 'M')->count();
        $female = PlantillaRecord::filled()->where('sex', 'F')->count();
        $total = $male + $female;
        $femalePct = $total > 0 ? round($female / $total * 100, 1) : 0;

        return [
            'key' => 'workforce_headcount_by_sex',
            'label' => 'Workforce Headcount (Sex-Disaggregated)',
            'value' => $total,
            'context' => "{$male} male / {$female} female",
            'insight' => $femalePct >= 40 && $femalePct <= 60
                ? "Female representation is {$femalePct}% — within the GAD-balanced 40-60% range."
                : "Female representation is {$femalePct}% — outside the GAD-balanced 40-60% range.",
            'owner' => 'GAD & Workforce / Analytics',
            'owner_route' => 'gad.index',
        ];
    }

    private static function workforcePwdCount(): array
    {
        $pwd = PlantillaRecord::filled()->where('is_pwd', true)->count();
        $total = PlantillaRecord::filled()->count();
        $pct = $total > 0 ? round($pwd / $total * 100, 2) : 0;

        return [
            'key' => 'workforce_pwd_count',
            'label' => 'PWD Employees',
            'value' => $pwd,
            'context' => "{$pct}% of active workforce",
            'insight' => $pct >= 1.0
                ? "Meets the 1% PWD employment quota under R.A. 10524."
                : "Below the 1% PWD employment quota under R.A. 10524.",
            'owner' => 'GAD & Workforce / Analytics',
            'owner_route' => 'gad.index',
        ];
    }

    private static function workforceSoloParentCount(): array
    {
        $count = PlantillaRecord::filled()
            ->whereNotNull('solo_parent')->where('solo_parent', '!=', '')->where('solo_parent', '!=', '-')
            ->count();

        return [
            'key' => 'workforce_solo_parent_count',
            'label' => 'Solo Parent Employees',
            'value' => $count,
            'context' => $count > 0 ? "{$count} employee(s) with a solo-parent ID on file" : 'None on file for the active workforce.',
            'owner' => 'GAD & Workforce / Analytics',
            'owner_route' => 'gad.index',
        ];
    }
}
