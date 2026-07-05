<?php

namespace Database\Seeders;

use App\Models\DocumentRetentionRule;
use App\Models\RetentionSchedule;
use Illuminate\Database\Seeder;

/**
 * Module 1B.5 — seeds representative entries from the NAP General Records
 * Disposition Schedule (2023/2024 GRDS, NAP General Circular No. 5) and
 * links each existing IDCC doc_type_code rule to its canonical series.
 * The Records Officer can edit/add rows as NAP issues updates — this is a
 * starting reference set, not a claim of completeness.
 */
class RetentionScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'record_series_title' => '201 File / Core Personnel Records',
                'description' => 'Appointment papers, PDS, service record, and other core 201 documents.',
                'record_category' => '201',
                'time_value' => 'PERMANENT',
                'active_period' => 'While in service',
                'storage_period' => 'Indefinite — Confidential/Restricted per NAP Circular 4',
                'total_retention' => 'Permanent',
                'disposition_action' => 'PERMANENT_PRESERVATION',
                'legal_basis' => 'R.A. 9470; NAP GRDS; NAP Circular 4',
                'nap_grds_item_ref' => 'GRDS-201-01',
                'doc_type_codes' => ['APPT-33A'],
            ],
            [
                'record_series_title' => 'Personal Data Sheet (CSC Form 212)',
                'description' => 'Personal Data Sheet and updates.',
                'record_category' => '201',
                'time_value' => 'TEMPORARY',
                'active_period' => 'While in service',
                'storage_period' => '10 years after separation, then review',
                'total_retention' => 'Active tenure + 10 years post-separation review',
                'disposition_action' => 'REVIEW',
                'legal_basis' => 'NAP GRDS',
                'nap_grds_item_ref' => 'GRDS-201-05',
                'doc_type_codes' => ['PDS-212'],
            ],
            [
                'record_series_title' => 'Leave Records (CSC Form 6 and ledgers)',
                'description' => 'Application for Leave and the consolidated leave ledger.',
                'record_category' => 'Leave',
                'time_value' => 'TEMPORARY',
                'active_period' => 'While in service',
                'storage_period' => '10 years, per accounting/audit requirements',
                'total_retention' => '10 years; disposal only after verified consolidated ledger',
                'disposition_action' => 'DESTRUCTION',
                'legal_basis' => 'Omnibus Rules on Leave; COA audit rules',
                'nap_grds_item_ref' => 'GRDS-LV-01',
                'doc_type_codes' => ['LEAVE-CSC6'],
            ],
            [
                'record_series_title' => 'Disciplinary / Administrative Case Records',
                'description' => 'Formal charges, decisions, and related RACCS-confidential case files.',
                'record_category' => 'Disciplinary',
                'time_value' => 'PERMANENT',
                'active_period' => 'Duration of case + appeal period',
                'storage_period' => 'Permanent if dismissal/major penalty; long-term cap otherwise (review before disposal)',
                'total_retention' => 'Permanent (conservative default) — review before any disposal',
                'disposition_action' => 'PERMANENT_PRESERVATION',
                'legal_basis' => 'CSC Res. 2500357 (RRACCS); R.A. 9470',
                'nap_grds_item_ref' => 'GRDS-DISC-01',
                'doc_type_codes' => ['DISC-RACCS'],
            ],
            [
                'record_series_title' => 'LGU Executive Orders / Memoranda / Ordinances',
                'description' => 'Local legislative and executive issuances.',
                'record_category' => 'LGU',
                'time_value' => 'TEMPORARY',
                'active_period' => '5 years',
                'storage_period' => '10 years in storage',
                'total_retention' => '15 years',
                'disposition_action' => 'REVIEW',
                'legal_basis' => 'R.A. 7160 (Local Government Code); NAP GRDS',
                'nap_grds_item_ref' => 'GRDS-LGU-01',
                'doc_type_codes' => ['LGU-EO'],
            ],
            [
                'record_series_title' => 'Routine Certifications and Requests',
                'description' => 'Certificate of Employment requests and similarly routine correspondence.',
                'record_category' => 'Financial',
                'time_value' => 'TEMPORARY',
                'active_period' => '1 year',
                'storage_period' => '2 years',
                'total_retention' => '3 years',
                'disposition_action' => 'DESTRUCTION',
                'legal_basis' => 'NAP GRDS',
                'nap_grds_item_ref' => 'GRDS-GEN-01',
                'doc_type_codes' => ['COE-REQ', 'OTHER'],
            ],
        ];

        foreach ($rows as $row) {
            $docTypeCodes = $row['doc_type_codes'];
            unset($row['doc_type_codes']);

            $series = RetentionSchedule::updateOrCreate(
                ['record_series_title' => $row['record_series_title']],
                $row
            );

            DocumentRetentionRule::whereIn('doc_type_code', $docTypeCodes)
                ->update(['record_series_id' => $series->id]);
        }
    }
}
