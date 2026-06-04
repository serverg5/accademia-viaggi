<?php

namespace App\Console\Commands;

use Database\Seeders\DemoDataSeeder;
use Illuminate\Console\Command;

class SeedDemoData extends Command
{
    protected $signature = 'demo:seed {--force : Consente l\'esecuzione anche in produzione}';

    protected $description = 'Seed demo data for local manual testing.';

    public function handle(): int
    {
        if (app()->isProduction() && ! $this->option('force')) {
            $this->error('Comando bloccato: demo:seed non viene eseguito in produzione senza --force.');

            return self::FAILURE;
        }

        if (app()->isProduction()) {
            config(['demo.allow_production_seed' => true]);
        }

        $this->call('db:seed', [
            '--class' => DemoDataSeeder::class,
        ]);

        return self::SUCCESS;
    }
}
