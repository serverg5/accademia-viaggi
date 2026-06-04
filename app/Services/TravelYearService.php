<?php

namespace App\Services;

use App\Models\TravelYear;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class TravelYearService
{
    public function ensureCurrentYear(?CarbonInterface $date = null): TravelYear
    {
        return $this->ensureYear((int) ($date ?? Carbon::now())->year);
    }

    public function ensureYear(int $year): TravelYear
    {
        return TravelYear::query()->firstOrCreate(
            ['year' => $year],
            [
                'status' => TravelYear::STATUS_ACTIVE,
                'is_locked' => false,
            ],
        );
    }
}
