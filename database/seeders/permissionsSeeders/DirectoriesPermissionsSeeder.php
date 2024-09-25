<?php

namespace Database\Seeders\PermissionsSeeders;

use App\Traits\PermissionSeederTrait;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DirectoriesPermissionsSeeder extends Seeder
{
    use PermissionSeederTrait;

    /**
     * Run the database seeds.
     * @return void
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->clearStoragePublicDirectory();
            $this->processPermission('seeders/json/permissions/directoriesPermissions.json');
        });
    }

    /**
     * Clear the storage/app/public directory.
     * @return void
     */
    private function clearStoragePublicDirectory(): void
    {
        $directory = storage_path('app/public');

        if (File::exists($directory)) {
            File::cleanDirectory($directory);
        }
    }
}
