<?php

namespace Database\Seeders\permissionsSeeders;

use App\Traits\PermissionSeederTrait;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommentsPermissionsSeeders extends Seeder
{
    use PermissionSeederTrait;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->processPermission('seeders/json/permissions/commentsPermissions.json');
        });
    }
}
