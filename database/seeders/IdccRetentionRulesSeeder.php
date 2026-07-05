<?php

namespace Database\Seeders;

use App\Models\DocumentRetentionRule;
use Illuminate\Database\Seeder;

/** Module 9 Stage 8 — R.A. 9470 + NAP RDS retention registry per doc_type_code. */
class IdccRetentionRulesSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            ['doc_type_code' => 'APPT-33A', 'rule_label' => 'Appointment (33-A/B) & core 201 — Permanent', 'retention_years' => null, 'is_permanent' => true, 'disposal_requires_ledger_verification' => false],
            ['doc_type_code' => 'PDS-212', 'rule_label' => 'Personal Data Sheet — active tenure, then post-separation review', 'retention_years' => 10, 'is_permanent' => false, 'disposal_requires_ledger_verification' => false],
            ['doc_type_code' => 'LEAVE-CSC6', 'rule_label' => 'Leave (CSC Form 6) — per accounting/audit; disposal only after verified consolidated ledger', 'retention_years' => 10, 'is_permanent' => false, 'disposal_requires_ledger_verification' => true],
            ['doc_type_code' => 'DISC-RACCS', 'rule_label' => 'Disciplinary — permanent if dismissal/major penalty; long-term cap otherwise (review before disposal)', 'retention_years' => null, 'is_permanent' => true, 'disposal_requires_ledger_verification' => false],
            ['doc_type_code' => 'LGU-EO', 'rule_label' => 'LGU EO/Memo/Ordinance — long-term administrative retention', 'retention_years' => 15, 'is_permanent' => false, 'disposal_requires_ledger_verification' => false],
            ['doc_type_code' => 'COE-REQ', 'rule_label' => 'Certificate of Employment request — routine, short retention', 'retention_years' => 3, 'is_permanent' => false, 'disposal_requires_ledger_verification' => false],
            ['doc_type_code' => 'OTHER', 'rule_label' => 'Unclassified — manual review before any disposal', 'retention_years' => 10, 'is_permanent' => false, 'disposal_requires_ledger_verification' => false],
        ];

        foreach ($rules as $rule) {
            DocumentRetentionRule::updateOrCreate(['doc_type_code' => $rule['doc_type_code']], $rule);
        }
    }
}
