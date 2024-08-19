<?php

namespace Database\Seeders\permissionsSeeders;

use App\Traits\PermissionSeederTrait;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersPermissionsSeeder extends Seeder
{
    use PermissionSeederTrait;

    /**
     * Run the database seeds.
     * @return void
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->processPermission('seeders/json/permissions/usersPermissions.json');
        });
    }
}
