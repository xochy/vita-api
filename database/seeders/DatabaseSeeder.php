<?php

namespace Database\Seeders;

use Database\Seeders\permissionsSeeders\PermissionsPermissionsSeeder;
use Database\Seeders\permissionsSeeders\PhysicalConditionsPermissionsSeeder;
use Database\Seeders\permissionsSeeders\RolesPermissionsSeeder;
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

        // seeders related to permissions
        $this->call(
            [
                // management permissions seeders
                UsersPermissionsSeeder::class,
                RolesPermissionsSeeder::class,
                PermissionsPermissionsSeeder::class,

                // models permissions seeders
                PhysicalConditionsPermissionsSeeder::class,
            ]
        );

        // seederes related with catalogs
        $this->call(
            [
                GoalSeeder::class,
                MuscleSeeder::class,
                CategorySeeder::class,
                FrequencySeeder::class,
                PhysicalConditionSeeder::class,
            ]
        );

        $this->call(
            [
                // 1. workouts are needed for routines
                WorkoutSeeder::class,
                // 2. make sure to seed variations before routines (they are related with workouts)
                VariationSeeder::class,
                // 3. routines are needed for plans
                RoutineSeeder::class,
                // 4. plans are needed for users
                PlanSeeder::class,
            ]
        );

        $this->call(UserSeeder::class,);
    }
}
