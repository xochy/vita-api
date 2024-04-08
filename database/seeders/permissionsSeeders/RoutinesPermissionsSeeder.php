<?php

namespace Database\Seeders\permissionsSeeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoutinesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Roles
        $user           = Role::where('name', 'user')->first();
        $adminRole      = Role::where('name', 'admin')->first();
        $superAdminRole = Role::where('name', 'superAdmin')->first();

        // Permission for reading routines
        Permission::create(
            [
                'name'         => 'read routines',
                'display_name' => 'Leer rutinas',
                'action'       => 'read',
                'subject'      => 'routine'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole,
                $user
            ]
        );

        // Permission for creating routines
        Permission::create(
            [
                'name'         => 'create routines',
                'display_name' => 'Crear rutinas',
                'action'       => 'create',
                'subject'      => 'routine'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole,
                $user
            ]
        );

        // Permission for updating routines
        Permission::create(
            [
                'name'         => 'update routines',
                'display_name' => 'Actualizar rutinas',
                'action'       => 'update',
                'subject'      => 'routine'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole,
                $user
            ]
        );

        // Permission for deleting routines
        Permission::create(
            [
                'name'         => 'delete routines',
                'display_name' => 'Eliminar rutinas',
                'action'       => 'delete',
                'subject'      => 'routine'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole,
                $user
            ]
        );

        // Permission for restoring routines
        Permission::create(
            [
                'name'         => 'restore routines',
                'display_name' => 'Restaurar rutinas',
                'action'       => 'restore',
                'subject'      => 'routine'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole,
                $user
            ]
        );

        // Permission for force deleting routines
        Permission::create(
            [
                'name'         => 'force delete routines',
                'display_name' => 'Eliminar rutinas permanentemente',
                'action'       => 'force delete',
                'subject'      => 'routine'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole,
                $user
            ]
        );
    }
}
