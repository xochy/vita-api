<?php

namespace Database\Seeders\permissionsSeeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UsersPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Roles
        $adminRole      = Role::where('name', 'admin')->first();
        $superAdminRole = Role::where('name', 'superAdmin')->first();

        // Permission for reading users
        Permission::create(
            [
                'name'         => 'read users',
                'display_name' => 'Leer usuarios',
                'action'       => 'read',
                'subject'      => 'user'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for creating users
        Permission::create(
            [
                'name'         => 'create users',
                'display_name' => 'Crear usuarios',
                'action'       => 'create',
                'subject'      => 'user'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for updating users
        Permission::create(
            [
                'name'         => 'update users',
                'display_name' => 'Actualizar usuarios',
                'action'       => 'update',
                'subject'      => 'user'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for deleting users
        Permission::create(
            [
                'name'         => 'delete users',
                'display_name' => 'Eliminar usuarios',
                'action'       => 'delete',
                'subject'      => 'user'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for restoring users
        Permission::create(
            [
                'name'         => 'restore users',
                'display_name' => 'Restaurar usuarios',
                'action'       => 'restore',
                'subject'      => 'user'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for force deleting users
        Permission::create(
            [
                'name'         => 'force delete users',
                'display_name' => 'Eliminar usuarios permanentemente',
                'action'       => 'force delete',
                'subject'      => 'user'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );
    }
}
