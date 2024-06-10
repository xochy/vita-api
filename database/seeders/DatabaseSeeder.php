<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\permissionsSeeders\UsersPermissionsSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(UsersPermissionsSeeder::class);
        $this->call(UserSeeder::class);

        $this->call(GoalSeeder::class);
        $this->call(FrequencySeeder::class);
        $this->call(PhysicalConditionSeeder::class);
    }
}
