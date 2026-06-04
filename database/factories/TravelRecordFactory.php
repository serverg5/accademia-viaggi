<?php

namespace Database\Factories;

use App\Models\TravelRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TravelRecord>
 */
class TravelRecordFactory extends Factory
{
    protected $model = TravelRecord::class;

    public function definition(): array
    {
        $date = fake()->dateTimeBetween('-6 months', '+1 month');
        $costs = fake()->randomFloat(2, 80, 1800);
        $sale = $costs + fake()->randomFloat(2, 40, 900);

        return [
            'travel_year_id' => null,
            'year' => (int) $date->format('Y'),
            'month' => (int) $date->format('n'),
            'record_date' => $date->format('Y-m-d'),
            'practice_code' => 'DEMO-' . fake()->unique()->numerify('####'),
            'values' => [
                'departure_from' => fake()->randomElement(['Milano', 'Roma', 'Napoli', 'Torino', 'Bologna']),
                'arrival' => fake()->randomElement(['Parigi', 'Londra', 'Madrid', 'Berlino', 'Amsterdam']),
                'transport' => fake()->randomElement(['aereo', 'treno', 'bus', 'auto', 'nave']),
                'costs' => $costs,
                'sale' => $sale,
            ],
            'created_by' => null,
            'updated_by' => null,
        ];
    }

    public function forPeriod(int $year, int $month): static
    {
        return $this->state(fn (): array => [
            'year' => $year,
            'month' => $month,
            'record_date' => sprintf('%d-%02d-%02d', $year, $month, fake()->numberBetween(1, 24)),
        ]);
    }
}
