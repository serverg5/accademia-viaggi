<?php

namespace App\Console\Commands;

use App\Services\TravelYearService;
use Illuminate\Console\Command;

class EnsureCurrentTravelYear extends Command
{
    protected $signature = 'travel-years:ensure-current';

    protected $description = 'Ensure the current travel year exists.';

    public function handle(TravelYearService $travelYearService): int
    {
        $travelYear = $travelYearService->ensureCurrentYear();

        $this->info("Anno viaggi {$travelYear->year} disponibile.");

        return self::SUCCESS;
    }
}
