<?php

namespace Database\Factories;

use App\Models\PlantillaRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlantillaRecordFactory extends Factory
{
    protected $model = PlantillaRecord::class;

    public function definition(): array
    {
        return [
            'last_name' => strtoupper($this->faker->lastName()),
            'first_name' => strtoupper($this->faker->firstName()),
            'employment_status' => 'P',
            'is_vacant' => false,
            'abolished' => false,
            'is_renewed' => true,
            'office_department' => $this->faker->company(),
        ];
    }
}
