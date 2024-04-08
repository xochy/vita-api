<?php

namespace Database\Seeders\permissionsSeeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PlansPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Roles
        $adminRole      = Role::where('name', 'admin')->first();
        $superAdminRole = Role::where('name', 'superAdmin')->first();

        // Permission for reading plans
        Permission::create(
            [
                'name'         => 'read plans',
                'display_name' => 'Leer planes',
                'action'       => 'read',
                'subject'      => 'plan'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for creating plans
        Permission::create(
            [
                'name'         => 'create plans',
                'display_name' => 'Crear planes',
                'action'       => 'create',
                'subject'      => 'plan'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for updating plans
        Permission::create(
            [
                'name'         => 'update plans',
                'display_name' => 'Actualizar planes',
                'action'       => 'update',
                'subject'      => 'plan'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for deleting plans
        Permission::create(
            [
                'name'         => 'delete plans',
                'display_name' => 'Eliminar planes',
                'action'       => 'delete',
                'subject'      => 'plan'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for restoring plans
        Permission::create(
            [
                'name'         => 'restore plans',
                'display_name' => 'Restaurar planes',
                'action'       => 'restore',
                'subject'      => 'plan'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for force deleting plans
        Permission::create(
            [
                'name'         => 'force delete plans',
                'display_name' => 'Eliminar permanentemente planes',
                'action'       => 'force delete',
                'subject'      => 'plan'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );
    }
}
