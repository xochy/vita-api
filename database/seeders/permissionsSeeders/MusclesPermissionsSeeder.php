<?php

namespace Database\Seeders\permissionsSeeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class MusclesPermissionsSeeder extends Seeder
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

        // Permission for reading muscles
        Permission::create(
            [
                'name'         => 'read muscles',
                'display_name' => 'Leer músculos',
                'action'       => 'read',
                'subject'      => 'muscle'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole,
                $user
            ]
        );

        // Permission for creating muscles
        Permission::create(
            [
                'name'         => 'create muscles',
                'display_name' => 'Crear músculos',
                'action'       => 'create',
                'subject'      => 'muscle'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for updating muscles
        Permission::create(
            [
                'name'         => 'update muscles',
                'display_name' => 'Actualizar músculos',
                'action'       => 'update',
                'subject'      => 'muscle'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for deleting muscles
        Permission::create(
            [
                'name'         => 'delete muscles',
                'display_name' => 'Eliminar músculos',
                'action'       => 'delete',
                'subject'      => 'muscle'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for restoring muscles
        Permission::create(
            [
                'name'         => 'restore muscles',
                'display_name' => 'Restaurar músculos',
                'action'       => 'restore',
                'subject'      => 'muscle'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for force deleting muscles
        Permission::create(
            [
                'name'         => 'force delete muscles',
                'display_name' => 'Eliminar permanentemente músculos',
                'action'       => 'force delete',
                'subject'      => 'muscle'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );
    }
}
