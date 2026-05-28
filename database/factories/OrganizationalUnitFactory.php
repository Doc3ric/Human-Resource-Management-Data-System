<?php

namespace Database\Factories;

use App\Models\OrganizationalUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrganizationalUnit>
 */
class OrganizationalUnitFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OrganizationalUnit::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(3, true),
            'code' => $this->faker->unique()->bothify('???-###'),
            'status' => $this->faker->randomElement(['Active', 'Inactive']),
        ];
    }

    /**
     * Indicate that the model should be active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Active',
        ]);
    }

    /**
     * Indicate that the model should be inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Inactive',
        ]);
    }
}
