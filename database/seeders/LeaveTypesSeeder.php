<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypesSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['code' => 'VL', 'name' => 'Vacation Leave', 'max_days_per_year' => 15, 'advance_notice_days' => 5],
            ['code' => 'SL', 'name' => 'Sick Leave', 'max_days_per_year' => 15, 'requires_medical_cert_over_days' => 5],
            ['code' => 'SPL', 'name' => 'Special Privilege Leave', 'max_days_per_year' => 3, 'advance_notice_days' => 7],
            ['code' => 'SLB', 'name' => 'Special Leave Benefits for Women', 'max_days_per_year' => 60],
        ];

        foreach ($types as $t) {
            LeaveType::updateOrCreate(['code' => $t['code']], $t);
        }
    }
}
