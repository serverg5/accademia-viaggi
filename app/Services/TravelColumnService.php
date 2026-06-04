<?php

namespace App\Services;

use App\Models\TravelColumn;
use Illuminate\Database\Eloquent\Collection;

class TravelColumnService
{
    public function visibleColumns(): Collection
    {
        return TravelColumn::query()
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function billingColumns(): Collection
    {
        return TravelColumn::query()
            ->where('is_visible', true)
            ->where('is_visible_in_billing', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function findByKey(string $key): ?TravelColumn
    {
        return TravelColumn::query()->where('key', $key)->first();
    }
}
