<?php

namespace Database\Seeders\permissionsSeeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class VariationsPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Roles
        $adminRole      = Role::where('name', 'admin')->first();
        $superAdminRole = Role::where('name', 'superAdmin')->first();
        $user           = Role::where('name', 'user')->first();

        // Permission for reading variations
        Permission::create(
            [
                'name'         => 'read variations',
                'display_name' => 'Leer variaciones',
                'action'       => 'read',
                'subject'      => 'variation'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole,
                $user
            ]
        );

        // Permission for creating variations
        Permission::create(
            [
                'name'         => 'create variations',
                'display_name' => 'Crear variaciones',
                'action'       => 'create',
                'subject'      => 'variation'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for updating variations
        Permission::create(
            [
                'name'         => 'update variations',
                'display_name' => 'Actualizar variaciones',
                'action'       => 'update',
                'subject'      => 'variation'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for deleting variations
        Permission::create(
            [
                'name'         => 'delete variations',
                'display_name' => 'Eliminar variaciones',
                'action'       => 'delete',
                'subject'      => 'variation'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for restoring variations
        Permission::create(
            [
                'name'         => 'restore variations',
                'display_name' => 'Restaurar variaciones',
                'action'       => 'restore',
                'subject'      => 'variation'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for force deleting variations
        Permission::create(
            [
                'name'         => 'force delete variations',
                'display_name' => 'Eliminar permanentemente variaciones',
                'action'       => 'force delete',
                'subject'      => 'variation'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );
    }
}
