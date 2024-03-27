<?php

namespace Database\Seeders\categories;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class CategoriesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Roles
        $adminRole      = Role::where('name', 'admin')->first();
        $superAdminRole = Role::where('name', 'superAdmin')->first();


        // Permission for reading categories
        Permission::create(
            [
                'name'         => 'read categories',
                'display_name' => 'Leer categorías',
                'action'       => 'read',
                'subject'      => 'category'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for creating categories
        Permission::create(
            [
                'name'         => 'create categories',
                'display_name' => 'Crear categorías',
                'action'       => 'create',
                'subject'      => 'category'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for updating categories
        Permission::create(
            [
                'name'         => 'update categories',
                'display_name' => 'Actualizar categorías',
                'action'       => 'update',
                'subject'      => 'category'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for deleting categories
        Permission::create(
            [
                'name'         => 'delete categories',
                'display_name' => 'Eliminar categorías',
                'action'       => 'delete',
                'subject'      => 'category'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for restoring categories
        Permission::create(
            [
                'name'         => 'restore categories',
                'display_name' => 'Restaurar categorías',
                'action'       => 'restore',
                'subject'      => 'category'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for force deleting categories
        Permission::create(
            [
                'name'         => 'force delete categories',
                'display_name' => 'Eliminar permanentemente categorías',
                'action'       => 'force delete',
                'subject'      => 'category'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );
    }
}
