<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::query()->delete();
        Permission::query()->delete();

        /* -------------------------------------------------------------------------- */
        /*                            Roles specifications                            */
        /* -------------------------------------------------------------------------- */

        // Super admin role
        Role::create(
            [
                'name'         => 'superAdmin',
                'display_name' => 'Super Administrador',
                'default'      => true,
            ]
        );

        // Admin role
        Role::create(
            [
                'name'         => 'admin',
                'display_name' => 'Administrador',
                'default'      => true,
            ]
        );

        // User role
        Role::create(
            [
                'name'         => 'user',
                'display_name' => 'Usuario',
                'default'      => true,
            ]
        );
    }
}
