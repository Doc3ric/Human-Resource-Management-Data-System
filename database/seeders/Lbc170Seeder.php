<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SalarySchedule;
use App\Models\SalaryGrade;

/**
 * Third Tranche Monthly Salary Schedule for the Civilian Personnel of the
 * National Government — Annex A of National Budget Circular No. 601 (DBM,
 * dated January 22, 2026), implementing the Third Tranche of the updated
 * Salary Schedule under EO No. 64, s.2024. Effective January 1, 2026.
 *
 * NOTE ON PROVENANCE: as of this writing, no LGU-specific Local Budget
 * Circular implementing the Third Tranche for local government personnel
 * has been issued (this data was previously mislabeled "LBC 170" in this
 * codebase — LBC 170 is actually the unrelated "Barangay Budgeting Manual,
 * 2026 Edition"). Under the pattern DBM used for the Second Tranche (LBC
 * 165), a future LGU-specific circular's "Annex A-1" (100% adoption, for
 * Special Cities and 1st Class Provinces/Cities) is expected to reproduce
 * this same Section-3/Annex-A national schedule. Bukidnon (1st Class
 * Province) would use these rates at 100% once its Sanggunian authorizes
 * Third Tranche implementation under such a circular — do not treat this
 * schedule as locally authorized until that circular exists.
 *
 * Grades 1-33, Steps 1-8 (SG 33 has only Steps 1-2): transcribed and
 * visually verified directly against the official DBM PDF (NBC 601, page 8,
 * Annex A).
 */
class Lbc170Seeder extends Seeder
{
    public function run()
    {
        $schedule = SalarySchedule::updateOrCreate(
            ['name' => '3rd Tranche'],
            [
                'law_name' => 'Executive Order No. 64',
                'lbc_number' => null, // no LGU-specific circular exists yet — see class doc comment
                'effective_date' => '2026-01-01',
                'description' => 'National schedule per NBC 601 (Jan 22, 2026), Annex A. Reserve/pending: not yet locally authorized for LGU implementation via a Local Budget Circular.',
                'is_active' => false, // reserve only — see class doc comment
            ]
        );

        // Clear existing rates for this schedule to avoid duplicates.
        // forceDelete (not delete): SalaryGrade uses SoftDeletes, but the
        // unique (schedule_id, grade, step) index doesn't exclude
        // soft-deleted rows, so a plain delete() would collide on reinsert.
        SalaryGrade::withTrashed()->where('salary_schedule_id', $schedule->id)->forceDelete();

        // Grade => [Step1 .. Step8]  (Pesos, monthly)
        $rates = [
            1  => [14634, 14730, 14849, 14968, 15089, 15211, 15333, 15456],
            2  => [15522, 15636, 15752, 15869, 15986, 16103, 16223, 16342],
            3  => [16486, 16610, 16732, 16856, 16982, 17106, 17234, 17360],
            4  => [17506, 17636, 17767, 17898, 18031, 18163, 18298, 18433],
            5  => [18581, 18720, 18858, 18998, 19137, 19280, 19423, 19565],
            6  => [19716, 19862, 20009, 20158, 20307, 20456, 20609, 20761],
            7  => [20914, 21069, 21224, 21382, 21539, 21699, 21859, 22022],
            8  => [22423, 22627, 22832, 23038, 23246, 23456, 23668, 23883],
            9  => [24329, 24523, 24720, 24917, 25117, 25318, 25521, 25725],
            10 => [26917, 27131, 27347, 27565, 27786, 28007, 28230, 28456],
            11 => [31705, 31820, 32109, 32401, 32697, 32998, 33302, 33611],
            12 => [33947, 34069, 34357, 34648, 34943, 35242, 35544, 35850],
            13 => [36125, 36283, 36599, 36919, 37244, 37572, 37904, 38241],
            14 => [38764, 39141, 39523, 39910, 40300, 40696, 41097, 41503],
            15 => [42178, 42594, 43015, 43442, 43874, 44310, 44753, 45202],
            16 => [45694, 46152, 46615, 47084, 47559, 48040, 48528, 49020],
            17 => [49562, 50066, 50576, 51092, 51614, 52144, 52678, 53221],
            18 => [53818, 54371, 54933, 55499, 56075, 56657, 57246, 57842],
            19 => [59153, 59966, 60793, 61632, 62486, 63353, 64236, 65132],
            20 => [66052, 66970, 67904, 68853, 69818, 70772, 71727, 72671],
            21 => [73303, 74337, 75388, 76456, 77542, 78645, 79692, 80831],
            22 => [81796, 82963, 84151, 85356, 86582, 87746, 89011, 90295],
            23 => [91306, 92622, 93962, 95330, 96823, 98341, 99883, 101318],
            24 => [102603, 104209, 105841, 107500, 109185, 110898, 112533, 114301],
            25 => [116643, 118469, 120326, 122212, 124131, 126079, 128061, 130073],
            26 => [131807, 133870, 135968, 138100, 140268, 142469, 144707, 146983],
            27 => [148940, 151273, 153644, 155906, 158353, 160235, 162752, 165310],
            28 => [167129, 169752, 172418, 174797, 177545, 180339, 182660, 185537],
            29 => [187531, 190482, 193480, 196528, 199624, 202005, 205191, 208430],
            30 => [210718, 214038, 217207, 220425, 223691, 227224, 230595, 234240],
            31 => [300961, 306691, 312532, 318182, 323938, 329989, 336092, 342310],
            32 => [356237, 363257, 370418, 377359, 384805, 392400, 400150, 408055],
            33 => [449157, 462329], // SG 33 has only Steps 1-2
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

        echo "NBC 601 3rd Tranche (Annex A) seeded successfully as RESERVE.\n";
    }
}
