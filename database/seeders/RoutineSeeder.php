<?php

namespace Database\Seeders;

use App\Models\Routine;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class RoutineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            DB::table('routines')->delete();

            $routinesJson = File::get(database_path('seeders/json/routines.json'));
            $routines = json_decode($routinesJson, true);

            foreach ($routines as $routineData) {
                $translations = $routineData['translations'];
                unset($routineData['translations']);

                $workouts = $routineData['workouts'];
                unset($routineData['workouts']);

                // Get workout IDs in a single query
                $workoutNames = collect($workouts)->pluck('workout');
                $workoutIds = DB::table('workouts')->whereIn('name', $workoutNames)->pluck('id', 'name');

                $routineWorkouts = collect($workouts)->map(function ($workout) use ($workoutIds) {
                    return [
                        'workout_id'  => $workoutIds[$workout['workout']],
                        'series'      => $workout['series'],
                        'repetitions' => $workout['repetitions'],
                        'time'        => $workout['time'],
                    ];
                });

                $routine = Routine::factory()->create($routineData);

                $routine->workouts()->attach($routineWorkouts);

                foreach ($translations as $translation) {
                    $routine->translations()->create($translation);
                }
            }
        });
    }
}
