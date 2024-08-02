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
                $translations = $workoutData['translations'];
                unset($workoutData['translations']);

                $subcategory = $workoutData['subcategory'];
                unset($workoutData['subcategory']);

                $subcategoryId = DB::table('subcategories')
                    ->where('name', $subcategory)
                    ->value('id');

                $workout = Workout::factory()->create(array_merge(
                    $workoutData,
                    [
                        'subcategory_id' => $subcategoryId
                    ]
                ));

                foreach ($translations as $translation) {
                    $workout->translations()->create($translation);
                }
            }
        });
    }
}
