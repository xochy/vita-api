<?php

namespace Database\Seeders;

use App\Models\Routine;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoutineSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            DB::table('routines')->delete();

            $routines = [
                [
                    'name' => 'Chest day',
                    'translations' => [
                        ['locale' => 'es', 'column' => 'name', 'translation' => 'Día de pecho'],
                    ],
                    'workouts' => [
                        [
                            'workout' => 'Bench press',
                            'series' => 4,
                            'repetitions' => 8,
                            'time' => 12,
                        ],
                        [
                            'workout' => 'Incline bench press',
                            'series' => 4,
                            'repetitions' => 8,
                            'time' => 15,
                        ],
                        [
                            'workout' => 'Dumbbell flyes',
                            'series' => 4,
                            'repetitions' => 10,
                            'time' => 10,
                        ],
                    ],
                ],
            ];

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
