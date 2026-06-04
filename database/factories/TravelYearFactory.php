<?php

namespace Database\Factories;

use App\Models\TravelYear;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TravelYear>
 */
class TravelYearFactory extends Factory
{
    protected $model = TravelYear::class;

    public function definition(): array
    {
        return [
            'year' => fake()->unique()->numberBetween(2020, 2090),
            'status' => TravelYear::STATUS_ACTIVE,
            'is_locked' => false,
        ];
    }

    public function archived(): static
    {
        return $this->state(fn (): array => [
            'status' => TravelYear::STATUS_ARCHIVED,
        ]);
    }

    public function locked(): static
    {
        return $this->state(fn (): array => [
            'is_locked' => true,
        ]);
    }
}
