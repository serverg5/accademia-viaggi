<?php

namespace Database\Seeders;

use App\Services\TravelYearService;
use Illuminate\Database\Seeder;

class TravelYearSeeder extends Seeder
{
    public function run(TravelYearService $travelYearService): void
    {
        $travelYearService->ensureCurrentYear();
    }
}
