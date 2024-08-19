<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->deleteExistingRoles();
            $this->deleteExistingPermissions();

            $roles = $this->getRolesFromJson();

            foreach ($roles as $roleData) {
                $this->processRole($roleData);
            }
        });
    }

    private function deleteExistingRoles(): void
    {
        DB::table('roles')->delete();
    }

    private function deleteExistingPermissions(): void
    {
        DB::table('permissions')->delete();
    }

    private function getRolesFromJson(): array
    {
        $rolesJson = File::get(database_path('seeders/json/roles.json'));
        return json_decode($rolesJson, true);
    }

    private function processRole(array $roleData): void
    {
        Role::create($roleData);
    }
}
