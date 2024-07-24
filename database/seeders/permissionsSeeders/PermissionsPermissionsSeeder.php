<?php

namespace Database\Seeders\permissionsSeeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Roles
        $superAdminRole = Role::where('name', 'superAdmin')->first();

        // Permission for reading permissions
        Permission::create(
            [
                'name'         => 'read permissions',
                'display_name' => 'Leer permisos',
                'action'       => 'read',
                'subject'      => 'permission'
            ]
        )->syncRoles(
            [
                $superAdminRole
            ]
        );

        // Permission for creating permissions
        Permission::create(
            [
                'name'         => 'create permissions',
                'display_name' => 'Crear permisos',
                'action'       => 'create',
                'subject'      => 'permission'
            ]
        )->syncRoles(
            [
                $superAdminRole
            ]
        );

        // Permission for updating permissions
        Permission::create(
            [
                'name'         => 'update permissions',
                'display_name' => 'Actualizar permisos',
                'action'       => 'update',
                'subject'      => 'permission'
            ]
        )->syncRoles(
            [
                $superAdminRole
            ]
        );

        // Permission for deleting permissions
        Permission::create(
            [
                'name'         => 'delete permissions',
                'display_name' => 'Eliminar permisos',
                'action'       => 'delete',
                'subject'      => 'permission'
            ]
        )->syncRoles(
            [
                $superAdminRole
            ]
        );

        // Permission for restoring permissions
        Permission::create(
            [
                'name'         => 'restore permissions',
                'display_name' => 'Restaurar permisos',
                'action'       => 'restore',
                'subject'      => 'permission'
            ]
        )->syncRoles(
            [
                $superAdminRole
            ]
        );

        // Permission for force deleting permissions
        Permission::create(
            [
                'name'         => 'force delete permissions',
                'display_name' => 'Eliminar permisos permanentemente',
                'action'       => 'forceDelete',
                'subject'      => 'permission'
            ]
        )->syncRoles(
            [
                $superAdminRole
            ]
        );
    }
}
