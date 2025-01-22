<?php

namespace Database\Seeders;

use Database\Seeders\PermissionsSeeders\CategoriesPermissionsSeeder;
use Database\Seeders\PermissionsSeeders\DirectoriesPermissionsSeeder;
use Database\Seeders\permissionsSeeders\FrequenciesPermissionsSeeder;
use Database\Seeders\permissionsSeeders\GoalsPermissionsSeeder;
use Database\Seeders\permissionsSeeders\MusclesPermissionsSeeder;
use Database\Seeders\permissionsSeeders\PermissionsPermissionsSeeder;
use Database\Seeders\permissionsSeeders\PhysicalConditionsPermissionsSeeder;
use Database\Seeders\permissionsSeeders\PlansPermissionsSeeder;
use Database\Seeders\permissionsSeeders\RolesPermissionsSeeder;
use Database\Seeders\permissionsSeeders\RoutinesPermissionsSeeder;
use Database\Seeders\permissionsSeeders\TranslationsPermissionsSeeder;
use Database\Seeders\permissionsSeeders\UsersPermissionsSeeder;
use Database\Seeders\permissionsSeeders\VariationsPermissionsSeeder;
use Database\Seeders\permissionsSeeders\WorkoutsPermissionsSeeder;
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

                // transalations permissions seeders
                TranslationsPermissionsSeeder::class,

                // models permissions seeders
                PlansPermissionsSeeder::class,
                GoalsPermissionsSeeder::class,
                MusclesPermissionsSeeder::class,
                WorkoutsPermissionsSeeder::class,
                RoutinesPermissionsSeeder::class,
                VariationsPermissionsSeeder::class,
                CategoriesPermissionsSeeder::class,
                FrequenciesPermissionsSeeder::class,
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

        $this->call([
            DirectorySeeder::class,
            DirectoriesPermissionsSeeder::class,
        ]);
    }
}
