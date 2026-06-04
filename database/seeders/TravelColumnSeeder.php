<?php

namespace Database\Seeders;

use App\Models\TravelColumn;
use Illuminate\Database\Seeder;

class TravelColumnSeeder extends Seeder
{
    public function run(): void
    {
        $columns = [
            [
                'key' => 'year',
                'label' => 'Anno',
                'type' => TravelColumn::TYPE_NUMBER,
                'is_required' => true,
                'is_system' => true,
                'is_deletable' => false,
            ],
            [
                'key' => 'month',
                'label' => 'Mese',
                'type' => TravelColumn::TYPE_NUMBER,
                'is_required' => true,
                'is_system' => true,
                'is_deletable' => false,
            ],
            [
                'key' => 'record_date',
                'label' => 'Data',
                'type' => TravelColumn::TYPE_DATE,
                'is_system' => true,
                'is_deletable' => false,
            ],
            [
                'key' => 'practice_code',
                'label' => 'Codice pratica',
                'type' => TravelColumn::TYPE_TEXT,
                'is_visible_in_billing' => true,
                'is_system' => true,
                'is_deletable' => false,
            ],
            [
                'key' => 'departure_from',
                'label' => 'Partenza da',
                'type' => TravelColumn::TYPE_TEXT,
            ],
            [
                'key' => 'arrival',
                'label' => 'Arrivo',
                'type' => TravelColumn::TYPE_TEXT,
            ],
            [
                'key' => 'transport',
                'label' => 'Mezzo',
                'type' => TravelColumn::TYPE_SELECT,
            ],
            [
                'key' => 'costs',
                'label' => 'Costi',
                'type' => TravelColumn::TYPE_MONEY,
                'is_visible_in_billing' => true,
                'is_system' => true,
                'is_deletable' => false,
            ],
            [
                'key' => 'sale',
                'label' => 'Vendita',
                'type' => TravelColumn::TYPE_MONEY,
                'is_visible_in_billing' => true,
                'is_system' => true,
                'is_deletable' => false,
            ],
        ];

        foreach ($columns as $index => $column) {
            TravelColumn::query()->updateOrCreate(
                ['key' => $column['key']],
                array_merge([
                    'options' => null,
                    'is_required' => false,
                    'is_visible' => true,
                    'is_visible_in_billing' => false,
                    'is_system' => false,
                    'is_deletable' => true,
                    'sort_order' => ($index + 1) * 10,
                ], $column),
            );
        }
    }
}
