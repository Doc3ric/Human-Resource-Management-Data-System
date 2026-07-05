<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HrmpsbRatingScale;

class HrmpsbRatingScalesSeeder extends Seeder
{
    public function run(): void
    {
        HrmpsbRatingScale::truncate();

        $rows = [];

        // -------------------------------------------------------------------------
        // IPCR RATING — common to both categories
        // -------------------------------------------------------------------------
        $ipcr_common = [
            ['Outstanding (5.00)',     5.00, 5.00,  10.00],
            ['Very Satisfactory (4.90 – 4.99)', 4.90, 4.99, 9.50],
            ['Very Satisfactory (4.80 – 4.89)', 4.80, 4.89, 9.00],
            ['Very Satisfactory (4.70 – 4.79)', 4.70, 4.79, 8.50],
            ['Very Satisfactory (4.60 – 4.69)', 4.60, 4.69, 8.00],
            ['Very Satisfactory (4.50 – 4.59)', 4.50, 4.59, 7.50],
            ['Very Satisfactory (4.40 – 4.49)', 4.40, 4.49, 7.00],
            ['Very Satisfactory (4.30 – 4.39)', 4.30, 4.39, 6.50],
            ['Very Satisfactory (4.20 – 4.29)', 4.20, 4.29, 6.00],
            ['Very Satisfactory (4.10 – 4.19)', 4.10, 4.19, 5.50],
            ['Very Satisfactory (4.00 – 4.09)', 4.00, 4.09, 5.00],
            ['N/A (No IPCR)',                   null,  null, 0.00],
        ];
        foreach ($ipcr_common as [$name, $min, $max, $pts]) {
            $rows[] = ['position_category'=>'all','criterion'=>'ipcr','level'=>null,'condition_name'=>$name,'min_value'=>$min,'max_value'=>$max,'points'=>$pts];
        }

        // Extra IPCR row for No Eligibility only
        $rows[] = ['position_category'=>'no_eligibility','criterion'=>'ipcr','level'=>null,'condition_name'=>'Satisfactory (3.60 – 3.99)','min_value'=>3.60,'max_value'=>3.99,'points'=>4.50];

        // -------------------------------------------------------------------------
        // AWARDS / RECOGNITION — same for both categories
        // -------------------------------------------------------------------------
        $awards = [
            ['Individual – CSC/National/International Award',        3.75],
            ['Individual – Agency/Provincial Govt (3 or more)',      3.00],
            ['Individual – Agency/Provincial Govt (2 awards)',       2.75],
            ['Individual – Agency/Provincial Govt (1 award)',        2.50],
            ['Individual – Office/Department (3 or more)',           2.00],
            ['Individual – Office/Department (2 awards)',            1.75],
            ['Individual – Office/Department (1 award)',             1.50],
            ['Individual – Private Org/GO/NGO (3 or more)',         1.00],
            ['Individual – Private Org/GO/NGO (2 awards)',          0.75],
            ['Individual – Private Org/GO/NGO (1 award)',           0.50],
            ['Group – CSC/National/International Award',            1.25],
            ['Group – Agency/Provincial Govt',                      1.00],
            ['Group – Office/Department',                           0.75],
            ['Group – Private Org/GO/NGO',                         0.50],
            ['None / No Award',                                     0.00],
        ];
        foreach ($awards as [$name, $pts]) {
            $rows[] = ['position_category'=>'all','criterion'=>'awards','level'=>null,'condition_name'=>$name,'min_value'=>null,'max_value'=>null,'points'=>$pts];
        }

        // -------------------------------------------------------------------------
        // EDUCATION — WITH ELIGIBILITY: First Level (Admin/Clerical, SG 1-10)
        // -------------------------------------------------------------------------
        $edu_first = [
            ['Doctorate Degree',                                  15.00],
            ['Doctorate (CAR – Candidate for Advancement)',       14.00],
            ['Doctorate w/ Academic Units',                       13.00],
            ['Master\'s Degree',                                  12.00],
            ['Master\'s (CAR – Candidate for Advancement)',       11.00],
            ['Master\'s (19 – 35 units)',                         10.00],
            ['Master\'s (1 – 18 units)',                           9.50],
            ['Bachelor\'s Degree Graduate',                        9.00],
            ['Bachelor\'s Degree (3rd or 4th Year Level)',         8.00],
            ['Meets the Minimum QS for Education',                 7.00],
        ];
        foreach ($edu_first as [$name, $pts]) {
            $rows[] = ['position_category'=>'eligibility','criterion'=>'education','level'=>'first_level','condition_name'=>$name,'min_value'=>null,'max_value'=>null,'points'=>$pts];
        }

        // EDUCATION — WITH ELIGIBILITY: Second Level (SG 11 and above)
        $edu_second = [
            ['Doctoral (Graduate)',                                15.00],
            ['Doctoral (CAR – Candidate for Advancement)',        14.00],
            ['Doctoral (41 – 59 units)',                          13.00],
            ['Doctoral (36 – 40 units)',                          12.75],
            ['Doctoral (20 – 35 units)',                          12.50],
            ['Doctoral (1 – 19 units)',                           12.00],
            ['Master\'s (Graduate)',                              11.00],
            ['Master\'s (CAR – Candidate for Advancement)',       10.50],
            ['Master\'s (25 – 35 units)',                         10.00],
            ['Master\'s (15 – 24 units)',                          9.50],
            ['Master\'s (1 – 14 units)',                           9.00],
            ['Meets the Minimum QS for Education',                 8.00],
        ];
        foreach ($edu_second as [$name, $pts]) {
            $rows[] = ['position_category'=>'eligibility','criterion'=>'education','level'=>'second_level','condition_name'=>$name,'min_value'=>null,'max_value'=>null,'points'=>$pts];
        }

        // EDUCATION — WITHOUT ELIGIBILITY (max 20 pts)
        $edu_no_elig = [
            ['Bachelor\'s Degree (Graduate) or Higher Degree',          20.00],
            ['Bachelor\'s Degree (3rd Year or Graduated Vocational)',   19.50],
            ['Bachelor\'s Degree (2nd Year Level)',                     19.00],
            ['Bachelor\'s Degree (1st Year Level)',                     18.50],
            ['High School Graduate',                                    17.00],
            ['High School Level (did not graduate)',                    16.50],
            ['Elementary School Graduate',                              15.00],
            ['Elementary Level (did not graduate)',                     14.50],
            ['Able to Read and Write',                                  13.00],
        ];
        foreach ($edu_no_elig as [$name, $pts]) {
            $rows[] = ['position_category'=>'no_eligibility','criterion'=>'education','level'=>null,'condition_name'=>$name,'min_value'=>null,'max_value'=>null,'points'=>$pts];
        }

        // -------------------------------------------------------------------------
        // RELEVANT EXPERIENCE — WITH ELIGIBILITY only (max 10 pts)
        // -------------------------------------------------------------------------
        $experience = [
            ['30 years or more',              null,  null, 10.00],
            ['25 yrs to 29 yrs & 11 mos',    25.00, 29.99,  9.00],
            ['20 yrs to 24 yrs & 11 mos',    20.00, 24.99,  8.00],
            ['15 yrs to 19 yrs & 11 mos',    15.00, 19.99,  7.00],
            ['10 yrs to 14 yrs & 11 mos',    10.00, 14.99,  6.00],
            ['5 yrs to 9 yrs & 11 mos',       5.00,  9.99,  5.00],
            ['4 yrs to 4 yrs & 11 mos',       4.00,  4.99,  4.00],
            ['3 yrs to 3 yrs & 11 mos',       3.00,  3.99,  3.00],
            ['2 yrs to 2 yrs & 11 mos',       2.00,  2.99,  2.00],
            ['1 yr to 1 yr & 11 mos',         1.00,  1.99,  1.00],
            ['0 to 11 months',                0.00,  0.99,  0.50],
            ['N/A (No relevant experience)',  null,  null,  0.00],
        ];
        foreach ($experience as [$name, $min, $max, $pts]) {
            $rows[] = ['position_category'=>'eligibility','criterion'=>'experience','level'=>null,'condition_name'=>$name,'min_value'=>$min,'max_value'=>$max,'points'=>$pts];
        }

        // -------------------------------------------------------------------------
        // RELEVANT TRAINING (Hours) — WITH ELIGIBILITY only (max 10 pts)
        // -------------------------------------------------------------------------
        $training = [
            ['81 hours or more',    81.00, null,  10.00],
            ['77 – 80 hours',       77.00, 80.00,  9.60],
            ['73 – 76 hours',       73.00, 76.00,  9.20],
            ['69 – 72 hours',       69.00, 72.00,  8.80],
            ['65 – 68 hours',       65.00, 68.00,  8.40],
            ['61 – 64 hours',       61.00, 64.00,  8.00],
            ['57 – 60 hours',       57.00, 60.00,  7.60],
            ['53 – 56 hours',       53.00, 56.00,  7.20],
            ['49 – 52 hours',       49.00, 52.00,  6.80],
            ['45 – 48 hours',       45.00, 48.00,  6.40],
            ['41 – 44 hours',       41.00, 44.00,  6.00],
            ['37 – 40 hours',       37.00, 40.00,  5.60],
            ['33 – 36 hours',       33.00, 36.00,  5.20],
            ['29 – 32 hours',       29.00, 32.00,  4.80],
            ['25 – 28 hours',       25.00, 28.00,  4.40],
            ['21 – 24 hours',       21.00, 24.00,  4.00],
            ['17 – 20 hours',       17.00, 20.00,  3.60],
            ['13 – 16 hours',       13.00, 16.00,  3.20],
            ['9 – 12 hours',         9.00, 12.00,  2.80],
            ['5 – 8 hours',          5.00,  8.00,  2.40],
            ['4 hours',              4.00,  4.00,  2.00],
            ['N/A (No relevant training)', null, null, 0.00],
        ];
        foreach ($training as [$name, $min, $max, $pts]) {
            $rows[] = ['position_category'=>'eligibility','criterion'=>'training','level'=>null,'condition_name'=>$name,'min_value'=>$min,'max_value'=>$max,'points'=>$pts];
        }

        // -------------------------------------------------------------------------
        // LENGTH OF SERVICE IN THE PGB — WITHOUT ELIGIBILITY only (max 15 pts)
        // -------------------------------------------------------------------------
        $los = [
            ['30+ years',        15.00],
            ['29+ to 30 years',  14.52],
            ['28+ to 29 years',  14.04],
            ['27+ to 28 years',  13.56],
            ['26+ to 27 years',  13.08],
            ['25+ to 26 years',  12.60],
            ['24+ to 25 years',  12.12],
            ['23+ to 24 years',  11.64],
            ['22+ to 23 years',  11.16],
            ['21+ to 22 years',  10.68],
            ['20+ to 21 years',  10.20],
            ['19+ to 20 years',   9.72],
            ['18+ to 19 years',   9.24],
            ['17+ to 18 years',   8.76],
            ['16+ to 17 years',   8.28],
            ['15+ to 16 years',   7.80],
            ['14+ to 15 years',   7.32],
            ['13+ to 14 years',   6.84],
            ['12+ to 13 years',   6.36],
            ['11+ to 12 years',   5.88],
            ['10+ to 11 years',   5.40],
            ['9+ to 10 years',    4.92],
            ['8+ to 9 years',     4.44],
            ['7+ to 8 years',     3.96],
            ['6+ to 7 years',     3.48],
            ['5+ to 6 years',     3.00],
            ['4+ to 5 years',     2.52],
            ['3+ to 4 years',     2.04],
            ['2+ to 3 years',     1.56],
            ['1+ to 2 years',     1.08],
            ['0 to 1 year',       0.60],
            ['N/A (No PGB service)', 0.00],
        ];
        foreach ($los as [$name, $pts]) {
            $rows[] = ['position_category'=>'no_eligibility','criterion'=>'length_of_service','level'=>null,'condition_name'=>$name,'min_value'=>null,'max_value'=>null,'points'=>$pts];
        }

        // -------------------------------------------------------------------------
        // DEMERITS — Suspension (within 5 years) — both categories
        // -------------------------------------------------------------------------
        $suspensions = [
            ['No Suspension',                                0.00],
            ['Suspension (1 day to 15 days)',               1.00],
            ['Suspension (16 days to 1 month)',             2.00],
            ['Suspension (1 month and 1 day to 3 months)', 3.00],
            ['Suspension (3 months and 1 day to 6 months)',4.00],
            ['Suspension (6 months and 1 day to 1 year)',  5.00],
        ];
        foreach ($suspensions as [$name, $pts]) {
            $rows[] = ['position_category'=>'all','criterion'=>'demerits_suspension','level'=>null,'condition_name'=>$name,'min_value'=>null,'max_value'=>null,'points'=>$pts];
        }

        // Reprimand — within 3 years; 0.75 each, max 2.25
        $rows[] = ['position_category'=>'all','criterion'=>'demerits_reprimand','level'=>null,
            'condition_name'=>'Per Reprimand (within 3 years)',
            'min_value'=>0.75,'max_value'=>2.25,'points'=>0.75];

        // Stern Warning — within 2 years; 0.50 each, max 1.50
        $rows[] = ['position_category'=>'all','criterion'=>'demerits_stern_warning','level'=>null,
            'condition_name'=>'Per Stern Warning (within 2 years)',
            'min_value'=>0.50,'max_value'=>1.50,'points'=>0.50];

        // Warning/Office Memo — 0.25 each, max 1.00 (period differs by category)
        $rows[] = ['position_category'=>'eligibility','criterion'=>'demerits_warning_memo','level'=>null,
            'condition_name'=>'Per Warning/Office Memo (within 2 years)',
            'min_value'=>0.25,'max_value'=>1.00,'points'=>0.25];
        $rows[] = ['position_category'=>'no_eligibility','criterion'=>'demerits_warning_memo','level'=>null,
            'condition_name'=>'Per Warning/Office Memo (within 3 years)',
            'min_value'=>0.25,'max_value'=>1.00,'points'=>0.25];

        // Bulk insert with timestamps
        $now = now()->toDateTimeString();
        foreach ($rows as &$row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }

        HrmpsbRatingScale::insert($rows);
    }
}
