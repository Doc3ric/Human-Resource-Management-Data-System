<?php

namespace Database\Factories;

use App\Models\Applicant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicantFactory extends Factory
{
    protected $model = Applicant::class;

    public function definition(): array
    {
        return [
            'reference_no' => $this->faker->unique()->numerify('APP-######'),
            'last_name' => strtoupper($this->faker->lastName()),
            'first_name' => strtoupper($this->faker->firstName()),
            'sex' => 'Male',
            'date_of_birth' => $this->faker->date(),
            'phone_number' => '09171234567',
            'address' => $this->faker->address(),
            'email_address' => $this->faker->unique()->safeEmail(),
            'position_applied' => 'ADMINISTRATIVE AIDE I',
            'office' => 'PHRMO',
            'highest_educational_attainment' => 'College Graduate',
        ];
    }
}
