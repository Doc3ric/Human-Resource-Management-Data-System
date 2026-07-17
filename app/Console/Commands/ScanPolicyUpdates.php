<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PolicyBulletin;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ScanPolicyUpdates extends Command
{
    protected $signature = 'hrdms:scan-policies';
    protected $description = 'Scan external sources for HR policy updates applicable to LGUs/Bukidnon.';

    public function handle()
    {
        $this->info('Starting HR policy scan...');

        // In a real-world scenario, this would parse actual RSS or scrape sites.
        // Given PHP/Laravel environment without a reliable API, we simulate 
        // fetching from a mock endpoint or mock array to demonstrate the filtering.

        $fetchedPolicies = [
            [
                'source_agency' => 'CSC',
                'reference_no' => 'MC No. 12, s. 2026',
                'title' => 'Guidelines on the Grant of Leave Privileges for Contract of Service Personnel in LGUs',
                'summary' => 'This memorandum circular outlines the new leave privileges specifically applicable to all Local Government Units (LGUs) and Provincial Governments.',
                'url' => 'https://csc.gov.ph/mc12-2026',
                'date_issued' => now()->subDays(2)->format('Y-m-d'),
            ],
            [
                'source_agency' => 'DBM',
                'reference_no' => 'LBC No. 168',
                'title' => 'Salary Schedule Implementation for First Class Provinces',
                'summary' => 'Updated salary schedules and compensation frameworks for 1st Class Provinces including Bukidnon for FY 2026.',
                'url' => 'https://dbm.gov.ph/lbc-168',
                'date_issued' => now()->subDay()->format('Y-m-d'),
            ],
            [
                'source_agency' => 'DILG',
                'reference_no' => 'MC No. 2026-045',
                'title' => 'Operational Guidelines for National Government Agencies',
                'summary' => 'These rules apply strictly to National Government Agencies (NGAs) and State Universities.',
                'url' => 'https://dilg.gov.ph/mc2026-045',
                'date_issued' => now()->format('Y-m-d'),
            ]
        ];

        $keywords = ['Local Government', 'LGU', 'Provincial', '1st Class', 'First Class Province', 'Bukidnon'];
        $excludeWords = ['National Government Agencies', 'NGA', 'GOCC', 'State Universities'];

        $newCount = 0;

        foreach ($fetchedPolicies as $policy) {
            $score = 0;
            $textToScan = strtolower($policy['title'] . ' ' . $policy['summary']);

            // Exclude rule
            $excluded = false;
            foreach ($excludeWords as $word) {
                if (Str::contains($textToScan, strtolower($word))) {
                    $excluded = true;
                    break;
                }
            }

            if ($excluded) {
                $this->line("Skipped (Excluded): " . $policy['title']);
                continue;
            }

            // Include rule
            foreach ($keywords as $word) {
                if (Str::contains($textToScan, strtolower($word))) {
                    $score += 10;
                }
            }

            if ($score > 0) {
                // Highly applicable policy found
                $exists = PolicyBulletin::where('url', $policy['url'])->exists();
                if (!$exists) {
                    PolicyBulletin::create([
                        'source_agency' => $policy['source_agency'],
                        'reference_no' => $policy['reference_no'],
                        'title' => $policy['title'],
                        'summary' => $policy['summary'],
                        'url' => $policy['url'],
                        'date_issued' => $policy['date_issued'],
                        'applicability_score' => $score,
                        'is_acknowledged' => false,
                    ]);
                    $newCount++;
                    $this->info("Saved Applicable Policy: " . $policy['title'] . " (Score: $score)");
                } else {
                    $this->line("Already exists: " . $policy['title']);
                }
            } else {
                $this->line("Skipped (Low Score): " . $policy['title']);
            }
        }

        $this->info("Scan complete. New applicable policies found: $newCount");
    }
}
