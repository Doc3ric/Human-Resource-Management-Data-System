<?php

namespace Database\Factories;

use App\Models\Training;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrainingFactory extends Factory
{
    protected $model = Training::class;

    public function definition(): array
    {
        return [
            'title' => 'Sample Training: ' . $this->faker->words(3, true),
            'type' => 'seminar',
            'date_from' => now()->subDays(10),
            'date_to' => now()->subDays(9),
            'total_hours' => 16,
        ];
    }
}
