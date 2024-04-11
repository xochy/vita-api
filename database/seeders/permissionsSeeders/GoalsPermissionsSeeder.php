<?php

namespace Database\Seeders\permissionsSeeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class GoalsPermissionsSeeder extends Seeder
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

        // Permission for reading goals
        Permission::create(
            [
                'name'         => 'read goals',
                'display_name' => 'Leer metas',
                'action'       => 'read',
                'subject'      => 'goal'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole,
                $user
            ]
        );

        // Permission for creating goals
        Permission::create(
            [
                'name'         => 'create goals',
                'display_name' => 'Crear metas',
                'action'       => 'create',
                'subject'      => 'goal'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for updating goals
        Permission::create(
            [
                'name'         => 'update goals',
                'display_name' => 'Actualizar metas',
                'action'       => 'update',
                'subject'      => 'goal'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for deleting goals
        Permission::create(
            [
                'name'         => 'delete goals',
                'display_name' => 'Eliminar metas',
                'action'       => 'delete',
                'subject'      => 'goal'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for restoring goals
        Permission::create(
            [
                'name'         => 'restore goals',
                'display_name' => 'Restaurar metas',
                'action'       => 'restore',
                'subject'      => 'goal'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for force deleting goals
        Permission::create(
            [
                'name'         => 'force delete goals',
                'display_name' => 'Eliminar metas permanentemente',
                'action'       => 'force delete',
                'subject'      => 'goal'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );
    }
}
