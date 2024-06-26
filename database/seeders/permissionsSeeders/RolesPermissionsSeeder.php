<?php

namespace Database\Seeders\permissionsSeeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Roles
        $superAdminRole = Role::where('name', 'superAdmin')->first();

        // Permission for reading roles
        Permission::create(
            [
                'name'         => 'read roles',
                'display_name' => 'Leer roles',
                'action'       => 'read',
                'subject'      => 'role'
            ]
        )->syncRoles(
            [
                $superAdminRole
            ]
        );

        // Permission for creating roles
        Permission::create(
            [
                'name'         => 'create roles',
                'display_name' => 'Crear roles',
                'action'       => 'create',
                'subject'      => 'role'
            ]
        )->syncRoles(
            [
                $superAdminRole
            ]
        );

        // Permission for updating roles
        Permission::create(
            [
                'name'         => 'update roles',
                'display_name' => 'Actualizar roles',
                'action'       => 'update',
                'subject'      => 'role'
            ]
        )->syncRoles(
            [
                $superAdminRole
            ]
        );

        // Permission for deleting roles
        Permission::create(
            [
                'name'         => 'delete roles',
                'display_name' => 'Eliminar roles',
                'action'       => 'delete',
                'subject'      => 'role'
            ]
        )->syncRoles(
            [
                $superAdminRole
            ]
        );

        // Permission for restoring roles
        Permission::create(
            [
                'name'         => 'restore roles',
                'display_name' => 'Restaurar roles',
                'action'       => 'restore',
                'subject'      => 'role'
            ]
        )->syncRoles(
            [
                $superAdminRole
            ]
        );

        // Permission for force deleting roles
        Permission::create(
            [
                'name'         => 'forceDelete roles',
                'display_name' => 'Eliminar permanentemente roles',
                'action'       => 'force delete',
                'subject'      => 'role'
            ]
        )->syncRoles(
            [
                $superAdminRole
            ]
        );
    }
}
