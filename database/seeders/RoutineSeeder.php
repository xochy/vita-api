<?php

namespace Database\Seeders;

use App\Models\Routine;
use App\Traits\HandlesTranslations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class RoutineSeeder extends Seeder
{
    use HandlesTranslations;

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
                $this->processRoutine($routineData);
            }
        });
    }

    private function processRoutine(array $routineData): void
    {
        $translations = $routineData['translations'];
        unset($routineData['translations']);

        $workouts = $routineData['workouts'];
        unset($routineData['workouts']);

        $routine = Routine::factory()->create($routineData);
        $this->attachWorkoutsToRoutine($routine, $workouts);
        $this->handleTranslations($routine, $translations);
    }

    private function attachWorkoutsToRoutine(Routine $routine, array $workouts): void
    {
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

        $routine->workouts()->attach($routineWorkouts);
    }
}
