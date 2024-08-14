<?php

namespace Database\Seeders;

use App\Models\Workout;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class WorkoutSeeder extends Seeder
{
    const PECTORALIS_MAJOR = 'Pectoralis Major';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            DB::table('workouts')->delete();

            $workoutsJson = File::get(database_path('seeders/json/workouts.json'));
            $workouts = json_decode($workoutsJson, true);

            foreach ($workouts as $workoutData) {
                $muscles = $workoutData['muscles'];
                unset($workoutData['muscles']);

                $translations = $workoutData['translations'];
                unset($workoutData['translations']);

                $category = $workoutData['category'];
                unset($workoutData['category']);

                $categoryId = DB::table('categories')
                    ->where('name', $category)
                    ->value('id');

                $workout = Workout::factory()->create(array_merge(
                    $workoutData,
                    [
                        'category_id' => $categoryId
                    ]
                ));

                foreach ($muscles as $muscle) {
                    $muscleId = DB::table('muscles')
                        ->where('name', $muscle['name'])
                        ->value('id');

                    $workout->muscles()->attach($muscleId, [
                        'priority' => $muscle['priority']
                    ]);
                }

                foreach ($translations as $translation) {
                    $workout->translations()->create($translation);
                }
            }
        });
    }
}
