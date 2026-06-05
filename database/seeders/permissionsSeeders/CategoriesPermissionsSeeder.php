<?php

namespace Database\Seeders\PermissionsSeeders;

use App\Traits\PermissionSeederTrait;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesPermissionsSeeder extends Seeder
{
    use PermissionSeederTrait;

    /**
     * Run the database seeds.
     * @return void
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->processPermission('seeders/json/permissions/categoriesPermissions.json');
        });
    }
}
