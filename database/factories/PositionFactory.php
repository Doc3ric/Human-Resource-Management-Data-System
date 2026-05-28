<?php

namespace Database\Factories;

use App\Models\Position;
use App\Models\OrganizationalUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Position>
 */
class PositionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Position::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organizational_unit_id' => OrganizationalUnit::factory(),
            'title' => $this->faker->jobTitle(),
            'code' => $this->faker->unique()->bothify('POS-###-???'),
            'salary_grade' => $this->faker->randomElement(['SG-1', 'SG-5', 'SG-10', 'SG-15', 'SG-20']),
            'abolished' => false,
            'status' => $this->faker->randomElement(['Active', 'Inactive']),
        ];
    }

    /**
     * Indicate that the position should be abolished.
     */
    public function abolished(): static
    {
        return $this->state(fn (array $attributes) => [
            'abolished' => true,
        ]);
    }

    /**
     * Indicate that the position should be active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Active',
        ]);
    }

    /**
     * Indicate that the position should be inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Inactive',
        ]);
    }
}
