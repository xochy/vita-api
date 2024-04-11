<?php

namespace Database\Seeders\permissionsSeeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FrequenciesPermissionsSeeder extends Seeder
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

        // Permission for reading frequencies
        Permission::create(
            [
                'name'         => 'read frequencies',
                'display_name' => 'Leer frecuencias',
                'action'       => 'read',
                'subject'      => 'frequency'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole,
                $user
            ]
        );

        // Permission for creating frequencies
        Permission::create(
            [
                'name'         => 'create frequencies',
                'display_name' => 'Crear frecuencias',
                'action'       => 'create',
                'subject'      => 'frequency'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for updating frequencies
        Permission::create(
            [
                'name'         => 'update frequencies',
                'display_name' => 'Actualizar frecuencias',
                'action'       => 'update',
                'subject'      => 'frequency'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for deleting frequencies
        Permission::create(
            [
                'name'         => 'delete frequencies',
                'display_name' => 'Eliminar frecuencias',
                'action'       => 'delete',
                'subject'      => 'frequency'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for restoring frequencies
        Permission::create(
            [
                'name'         => 'restore frequencies',
                'display_name' => 'Restaurar frecuencias',
                'action'       => 'restore',
                'subject'      => 'frequency'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for force deleting frequencies
        Permission::create(
            [
                'name'         => 'force delete frequencies',
                'display_name' => 'Eliminar permanentemente frecuencias',
                'action'       => 'force delete',
                'subject'      => 'frequency'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );
    }
}
