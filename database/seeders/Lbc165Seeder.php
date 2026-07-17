<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SalarySchedule;
use App\Models\SalaryGrade;

/**
 * Second Tranche Monthly Salary Schedule for Local Government Personnel
 * In Special Cities and First Class Provinces and Cities — Annex A-1 of
 * Local Budget Circular No. 165 (DBM, dated July 18, 2025), implementing
 * the Second Tranche of the updated Salary Schedule under EO No. 64, s.2024.
 * Effective January 1, 2025.
 *
 * Bukidnon is a 1st Class Province (DOF classification, effective Jan 31,
 * 2025), so Annex A-1 is its applicable table under LBC 165 Item 5.1.1/5.1.2.
 *
 * Grades 1-30, Steps 1-8: transcribed and visually verified directly against
 * the official DBM PDF (page 9, Annex A-1). Grades 31-33 are NOT listed in
 * Annex A-1 (the annex tops out at SG 30) — the values below for 31-33 are
 * carried over from this codebase's prior data and are UNVERIFIED against
 * this circular; confirm them against an authoritative source before relying
 * on them for SG 31-33 payroll.
 */
class Lbc165Seeder extends Seeder
{
    public function run()
    {
        $schedule = SalarySchedule::updateOrCreate(
            ['lbc_number' => 'LBC 165'],
            [
                'name' => '2nd Tranche',
                'law_name' => 'Executive Order No. 64',
                'effective_date' => '2025-01-01',
                'description' => 'Annex A-1 (Special Cities and First Class Provinces and Cities) — applicable to Bukidnon as a 1st Class Province.',
                'is_active' => true,
            ]
        );

        // Deactivate others
        SalarySchedule::where('id', '!=', $schedule->id)->update(['is_active' => false]);

        // Clear existing rates for this schedule to avoid duplicates.
        // forceDelete (not delete): SalaryGrade uses SoftDeletes, but the
        // unique (schedule_id, grade, step) index doesn't exclude
        // soft-deleted rows, so a plain delete() would collide on reinsert.
        SalaryGrade::withTrashed()->where('salary_schedule_id', $schedule->id)->forceDelete();

        // Grade => [Step1 .. Step8]  (Pesos, monthly)
        $rates = [
            1  => [14061, 14164, 14278, 14393, 14509, 14626, 14743, 14862],
            2  => [14925, 15035, 15146, 15258, 15371, 15484, 15599, 15714],
            3  => [15852, 15971, 16088, 16208, 16329, 16448, 16571, 16693],
            4  => [16833, 16958, 17084, 17209, 17337, 17464, 17594, 17724],
            5  => [17866, 18000, 18133, 18267, 18401, 18538, 18676, 18813],
            6  => [18957, 19098, 19239, 19383, 19526, 19670, 19816, 19963],
            7  => [20110, 20258, 20408, 20560, 20711, 20865, 21019, 21175],
            8  => [21448, 21642, 21839, 22035, 22234, 22435, 22638, 22843],
            9  => [23226, 23411, 23599, 23788, 23978, 24170, 24364, 24558],
            10 => [25586, 25790, 25996, 26203, 26412, 26623, 26835, 27050],
            11 => [30024, 30308, 30597, 30889, 31185, 31486, 31790, 32099],
            12 => [32245, 32529, 32817, 33108, 33403, 33702, 34004, 34310],
            13 => [34421, 34733, 35049, 35369, 35694, 36022, 36354, 36691],
            14 => [37024, 37384, 37749, 38118, 38491, 38869, 39252, 39640],
            15 => [40208, 40604, 41006, 41413, 41824, 42241, 42662, 43090],
            16 => [43560, 43996, 44438, 44885, 45338, 45796, 46261, 46730],
            17 => [47247, 47727, 48213, 48705, 49203, 49708, 50218, 50735],
            18 => [51304, 51832, 52367, 52907, 53456, 54010, 54572, 55140],
            19 => [56390, 57165, 57953, 58753, 59567, 60394, 61235, 62089],
            20 => [62967, 63842, 64732, 65637, 66557, 67479, 68409, 69342],
            21 => [70013, 71000, 72004, 73024, 74061, 75115, 76151, 77239],
            22 => [78162, 79277, 80411, 81564, 82735, 83887, 85096, 86324],
            23 => [87315, 88574, 89855, 91163, 92592, 94043, 95518, 96955],
            24 => [98185, 99721, 101283, 102871, 104483, 106123, 107739, 109431],
            25 => [111727, 113476, 115254, 117062, 118899, 120766, 122664, 124591],
            26 => [126252, 128228, 130238, 132280, 134356, 136465, 138608, 140788],
            27 => [142663, 144897, 147169, 149407, 151752, 153850, 156267, 158723],
            28 => [160469, 162988, 165548, 167994, 170634, 173320, 175803, 178572],
            29 => [180492, 183332, 186218, 189151, 192131, 194797, 197870, 200993],
            30 => [203200, 206401, 209558, 212766, 216022, 219434, 222797, 226319],
            // Not covered by Annex A-1 — carried over, unverified against LBC 165:
            31 => [293191, 298773, 304464, 310119, 315883, 321846, 327895, 334059],
            32 => [347888, 354743, 361736, 368694, 375969, 383391, 390963, 398686],
            33 => [438844, 451713], // SG 33 has only Steps 1-2
        ];

        $rows = [];
        foreach ($rates as $grade => $steps) {
            foreach ($steps as $stepIndex => $salary) {
                $rows[] = [
                    'salary_schedule_id' => $schedule->id,
                    'grade' => $grade,
                    'step' => $stepIndex + 1,
                    'monthly_salary' => $salary,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        $chunks = array_chunk($rows, 100);
        foreach ($chunks as $chunk) {
            SalaryGrade::insert($chunk);
        }

        echo "LBC 165 2nd Tranche (Annex A-1) seeded successfully.\n";
    }
}
