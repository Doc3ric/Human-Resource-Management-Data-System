<?php

namespace App\Support\Leave;

use App\Models\PlantillaRecord;

/**
 * Module 10-M2 cross-cutting guardrail — JO/COS personnel are excluded from
 * civil-service leave credit accrual entirely (DOLE-CSC-COA-DBM JC No. 1
 * s.2017), regardless of any renewal-signal state (Module 1A.4 note).
 */
class LeaveGuardrail
{
    public const BLOCK_MESSAGE = 'Transaction Aborted: Job Order and Contract of Service personnel are excluded from civil-service leave credit accrual workflows under DOLE-CSC-COA-DBM JC No. 1 s. 2017.';

    // NOTE: 'CT' in this app's employment_status values means Co-Terminous
    // (a civil-service appointment type, entitled to leave) — do not confuse
    // with "COS" (Contract of Service), which IS excluded here.
    private const EXCLUDED_STATUSES = ['JO', 'Job Order', 'J.O.', 'J', 'COS', 'C.O.S.'];

    public static function isExcluded(PlantillaRecord $record): bool
    {
        return in_array(strtoupper((string) $record->employment_status), array_map('strtoupper', self::EXCLUDED_STATUSES), true);
    }

    public static function assertNotExcluded(PlantillaRecord $record): void
    {
        if (self::isExcluded($record)) {
            throw new \RuntimeException(self::BLOCK_MESSAGE);
        }
    }
}
