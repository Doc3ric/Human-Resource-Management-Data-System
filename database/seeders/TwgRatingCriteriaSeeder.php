<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\TwgRatingCriterion;
use Illuminate\Database\Seeder;

/**
 * Module 6.1 — seeds the dynamic criteria library from TODAY's real,
 * live configuration (Setting::twg_max_* values), so the new engine starts
 * from the office's actual current point allocation rather than synthetic
 * defaults. Administrators can add/edit/deactivate criteria afterward
 * without a deploy — the whole point of making this a table, not columns.
 */
class TwgRatingCriteriaSeeder extends Seeder
{
    public function run(): void
    {
        $criteria = [
            [
                'criterion_key' => 'psb_interview',
                'criterion_name' => 'PSB Interview',
                'criterion_category' => 'all',
                'score_basis_description' => 'Computed from the average of all submitted panel interview evaluations.',
                'point_value' => (float) Setting::getVal('twg_max_psb_interview', 50),
                'rating_scale_key' => null,
                'sort_order' => 1,
            ],
            [
                'criterion_key' => 'ipcr',
                'criterion_name' => 'IPCR Rating',
                'criterion_category' => 'all',
                'score_basis_description' => 'Latest IPCR numeric rating and adjectival classification.',
                'point_value' => (float) Setting::getVal('twg_max_ipcr', 10),
                'rating_scale_key' => 'ipcr',
                'sort_order' => 2,
            ],
            [
                'criterion_key' => 'awards',
                'criterion_name' => 'Awards',
                'criterion_category' => 'all',
                'score_basis_description' => 'Highest award received, by conferring authority level.',
                'point_value' => (float) Setting::getVal('twg_max_awards', 5),
                'rating_scale_key' => 'awards',
                'sort_order' => 3,
            ],
            [
                'criterion_key' => 'education_eligibility',
                'criterion_name' => 'Education (With Eligibility)',
                'criterion_category' => 'eligibility',
                'score_basis_description' => 'Highest educational attainment, positions requiring eligibility.',
                'point_value' => (float) Setting::getVal('twg_max_education_eligibility', 15),
                'rating_scale_key' => 'education',
                'sort_order' => 4,
            ],
            [
                'criterion_key' => 'education_no_eligibility',
                'criterion_name' => 'Education (No Eligibility)',
                'criterion_category' => 'no_eligibility',
                'score_basis_description' => 'Highest educational attainment, positions not requiring eligibility.',
                'point_value' => (float) Setting::getVal('twg_max_education_no_eligibility', 20),
                'rating_scale_key' => 'education',
                'sort_order' => 5,
            ],
            [
                'criterion_key' => 'experience',
                'criterion_name' => 'Relevant Experience',
                'criterion_category' => 'all',
                'score_basis_description' => 'Total position-relevant work experience, per DBM-CSC JC No. 1 s.2017.',
                'point_value' => (float) Setting::getVal('twg_max_experience', 10),
                'rating_scale_key' => 'experience',
                'sort_order' => 6,
            ],
            [
                'criterion_key' => 'training',
                'criterion_name' => 'Relevant Training',
                'criterion_category' => 'all',
                'score_basis_description' => 'Total relevant training hours, by hour bracket.',
                'point_value' => (float) Setting::getVal('twg_max_training', 10),
                'rating_scale_key' => 'training',
                'sort_order' => 7,
            ],
            [
                'criterion_key' => 'length_of_service',
                'criterion_name' => 'Length of Service',
                'criterion_category' => 'all',
                'score_basis_description' => 'Total government service tenure.',
                'point_value' => (float) Setting::getVal('twg_max_length_of_service', 15),
                'rating_scale_key' => 'length_of_service',
                'sort_order' => 8,
            ],
        ];

        foreach ($criteria as $c) {
            TwgRatingCriterion::updateOrCreate(['criterion_key' => $c['criterion_key']], $c);
        }
    }
}
