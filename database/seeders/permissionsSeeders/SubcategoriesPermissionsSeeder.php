<?php

namespace Database\Seeders\permissionsSeeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SubcategoriesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Roles
        $adminRole      = Role::where('name', 'admin')->first();
        $superAdminRole = Role::where('name', 'superAdmin')->first();

        // Permission for reading subcategories
        Permission::create(
            [
                'name'         => 'read subcategories',
                'display_name' => 'Leer subcategorías',
                'action'       => 'read',
                'subject'      => 'subcategory'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for creating subcategories
        Permission::create(
            [
                'name'         => 'create subcategories',
                'display_name' => 'Crear subcategorías',
                'action'       => 'create',
                'subject'      => 'subcategory'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for updating subcategories
        Permission::create(
            [
                'name'         => 'update subcategories',
                'display_name' => 'Actualizar subcategorías',
                'action'       => 'update',
                'subject'      => 'subcategory'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for deleting subcategories
        Permission::create(
            [
                'name'         => 'delete subcategories',
                'display_name' => 'Eliminar subcategorías',
                'action'       => 'delete',
                'subject'      => 'subcategory'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for restoring subcategories
        Permission::create(
            [
                'name'         => 'restore subcategories',
                'display_name' => 'Restaurar subcategorías',
                'action'       => 'restore',
                'subject'      => 'subcategory'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );

        // Permission for force deleting subcategories
        Permission::create(
            [
                'name'         => 'force delete subcategories',
                'display_name' => 'Eliminar permanentemente subcategorías',
                'action'       => 'force delete',
                'subject'      => 'subcategory'
            ]
        )->syncRoles(
            [
                $superAdminRole,
                $adminRole
            ]
        );
    }
}
