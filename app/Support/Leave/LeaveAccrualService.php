<?php

namespace App\Support\Leave;

use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\PlantillaRecord;

/**
 * Module 10-M2 — monthly VL/SL accrual per the Omnibus Rules on Leave
 * (CSC MC No. 41 s.1998): 1.25 days VL + 1.25 days SL per month of service
 * (2.5 days/month, 15 days/year each) for regular civil-service personnel.
 */
class LeaveAccrualService
{
    public const MONTHLY_ACCRUAL_PER_TYPE = 1.25;

    public function accrueMonth(PlantillaRecord $record, int $year): array
    {
        LeaveGuardrail::assertNotExcluded($record);

        $results = [];
        foreach (LeaveType::whereIn('code', ['VL', 'SL'])->get() as $type) {
            $balance = LeaveBalance::firstOrCreate(
                ['plantilla_record_id' => $record->id, 'leave_type_id' => $type->id, 'year' => $year],
                ['earned_days' => 0, 'used_days' => 0]
            );
            $balance->increment('earned_days', self::MONTHLY_ACCRUAL_PER_TYPE);
            $results[$type->code] = $balance->fresh();
        }

        return $results;
    }
}
