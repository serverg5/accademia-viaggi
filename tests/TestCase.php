<?php

namespace Tests;

use App\Models\User;
use App\Support\Roles;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\TravelColumnSeeder;
use Database\Seeders\TravelYearSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    protected function seedTravelDomain(): void
    {
        $this->seed([
            RoleAndPermissionSeeder::class,
            TravelColumnSeeder::class,
            TravelYearSeeder::class,
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function adminUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);

        return $user;
    }

    protected function operatorUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Roles::OPERATORE);

        return $user;
    }
}
