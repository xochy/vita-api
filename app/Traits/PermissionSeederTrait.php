<?php

namespace App\Traits;

use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

trait PermissionSeederTrait
{
    /**
     * Process the permission json file.
     * @param string $permissionJsonPath
     * @return void
     */
    private function processPermission(string $permissionJsonPath): void
    {
        $permissionsJson = File::get(database_path($permissionJsonPath));
        $permissions = json_decode($permissionsJson, true);

        foreach ($permissions as $permissionData) {
            $this->createPermission($permissionData);
        }
    }

    /**
     * Create a permission.
     * @param array $permissionData
     * @return void
     */
    private function createPermission(array $permissionData): void
    {
        $rolesData = $permissionData['roles'];
        unset($permissionData['roles']);

        $permission = Permission::create($permissionData);
        $this->syncRoles($permission, $rolesData);
    }

    /**
     * Sync the roles with the permission.
     * @param Permission $permission
     * @param array $rolesData
     * @return void
     */
    private function syncRoles(Permission $permission, array $rolesData): void
    {
        $roles = Role::whereIn('name', $rolesData)->get();
        $permission->syncRoles($roles);
    }
}
