<?php

namespace Database\Seeders;

use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Permissions::ALL as $permission) {
            Permission::query()->firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $admin = Role::query()->firstOrCreate([
            'name' => Roles::ADMIN,
            'guard_name' => 'web',
        ]);

        $operatore = Role::query()->firstOrCreate([
            'name' => Roles::OPERATORE,
            'guard_name' => 'web',
        ]);

        $admin->syncPermissions(Permissions::ALL);
        $operatore->syncPermissions(Permissions::OPERATORE);
    }
}
