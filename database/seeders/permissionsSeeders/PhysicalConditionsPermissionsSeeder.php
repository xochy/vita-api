<?php

namespace Database\Seeders\permissionsSeeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PhysicalConditionsPermissionsSeeder extends Seeder
{
    const SUBJECT = 'physical condition';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Roles
        $user           = Role::where('name', 'user')->first();
        $adminRole      = Role::where('name', 'admin')->first();
        $superAdminRole = Role::where('name', 'superAdmin')->first();

        // Permission for reading physical conditions
        Permission::create(
            [
                'name'         => 'read physical conditions',
                'display_name' => 'Leer condiciones físicas',
                'action'       => 'read',
                'subject'      => self::SUBJECT
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole,
                $user
            ]
        );

        // Permission for creating physical conditions
        Permission::create(
            [
                'name'         => 'create physical conditions',
                'display_name' => 'Crear condiciones físicas',
                'action'       => 'create',
                'subject'      => self::SUBJECT
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for updating physical conditions
        Permission::create(
            [
                'name'         => 'update physical conditions',
                'display_name' => 'Actualizar condiciones físicas',
                'action'       => 'update',
                'subject'      => self::SUBJECT
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for deleting physical conditions
        Permission::create(
            [
                'name'         => 'delete physical conditions',
                'display_name' => 'Eliminar condiciones físicas',
                'action'       => 'delete',
                'subject'      => self::SUBJECT
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for restoring physical conditions
        Permission::create(
            [
                'name'         => 'restore physical conditions',
                'display_name' => 'Restaurar condiciones físicas',
                'action'       => 'restore',
                'subject'      => self::SUBJECT
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for force deleting physical conditions
        Permission::create(
            [
                'name'         => 'force delete physical conditions',
                'display_name' => 'Eliminar permanentemente condiciones físicas',
                'action'       => 'force delete',
                'subject'      => self::SUBJECT
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );
    }
}
