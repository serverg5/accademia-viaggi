<?php

namespace Database\Factories;

use App\Models\TravelColumn;
use App\Models\TravelSelectOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TravelSelectOption>
 */
class TravelSelectOptionFactory extends Factory
{
    protected $model = TravelSelectOption::class;

    public function definition(): array
    {
        $label = fake()->unique()->word();

        return [
            'travel_column_id' => TravelColumn::query()
                ->where('type', TravelColumn::TYPE_SELECT)
                ->inRandomOrder()
                ->value('id') ?? TravelColumn::query()->firstOrCreate([
                    'key' => 'factory_select',
                ], [
                    'label' => 'Selezione factory',
                    'type' => TravelColumn::TYPE_SELECT,
                    'is_visible' => true,
                ])->getKey(),
            'label' => ucfirst($label),
            'value' => str($label)->slug()->toString(),
            'sort_order' => fake()->numberBetween(0, 100),
            'is_active' => true,
            'created_by' => null,
            'updated_by' => null,
        ];
    }
}
