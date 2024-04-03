<?php

namespace Database\Seeders\permissionsSeeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class WorkoutsPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Roles
        $adminRole      = Role::where('name', 'admin')->first();
        $superAdminRole = Role::where('name', 'superAdmin')->first();

        // Permission for reading workouts
        Permission::create(
            [
                'name'         => 'read workouts',
                'display_name' => 'Leer entrenamientos',
                'action'       => 'read',
                'subject'      => 'workout'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for creating workouts
        Permission::create(
            [
                'name'         => 'create workouts',
                'display_name' => 'Crear entrenamientos',
                'action'       => 'create',
                'subject'      => 'workout'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for updating workouts
        Permission::create(
            [
                'name'         => 'update workouts',
                'display_name' => 'Actualizar entrenamientos',
                'action'       => 'update',
                'subject'      => 'workout'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for deleting workouts
        Permission::create(
            [
                'name'         => 'delete workouts',
                'display_name' => 'Eliminar entrenamientos',
                'action'       => 'delete',
                'subject'      => 'workout'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for restoring workouts
        Permission::create(
            [
                'name'         => 'restore workouts',
                'display_name' => 'Restaurar entrenamientos',
                'action'       => 'restore',
                'subject'      => 'workout'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for force deleting workouts
        Permission::create(
            [
                'name'         => 'force delete workouts',
                'display_name' => 'Eliminar permanentemente entrenamientos',
                'action'       => 'force delete',
                'subject'      => 'workout'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );
    }
}
