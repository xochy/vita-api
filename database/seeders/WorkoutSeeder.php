<?php

namespace Database\Seeders;

use App\Models\Workout;
use App\Traits\HandlesTranslations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class WorkoutSeeder extends Seeder
{
    use HandlesTranslations;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->deleteExistingWorkouts();
            $workouts = $this->getWorkoutsFromJson();

            foreach ($workouts as $workoutData) {
                $this->processWorkout($workoutData);
            }
        });
    }

    private function deleteExistingWorkouts(): void
    {
        DB::table('workouts')->delete();
    }

    private function getWorkoutsFromJson(): array
    {
        $workoutsJson = File::get(database_path('seeders/json/workouts.json'));
        return json_decode($workoutsJson, true);
    }

    private function processWorkout(array $workoutData): void
    {
        $muscles = $workoutData['muscles'];
        unset($workoutData['muscles']);

        $translations = $workoutData['translations'];
        unset($workoutData['translations']);

        $category = $workoutData['category'];
        unset($workoutData['category']);

        $categoryId = $this->getCategoryIdByName($category);

        $workout = Workout::factory()->create(array_merge(
            $workoutData,
            ['category_id' => $categoryId]
        ));

        $this->attachMusclesToWorkout($workout, $muscles);
        $this->handleTranslations($workout, $translations);
    }

    private function getCategoryIdByName(string $categoryName): int
    {
        return DB::table('categories')
            ->where('name', $categoryName)
            ->value('id');
    }

    private function attachMusclesToWorkout(Workout $workout, array $muscles): void
    {
        foreach ($muscles as $muscle) {
            $muscleId = DB::table('muscles')
                ->where('name', $muscle['name'])
                ->value('id');

            $workout->muscles()->attach($muscleId, [
                'priority' => $muscle['priority']
            ]);
        }
    }
}
