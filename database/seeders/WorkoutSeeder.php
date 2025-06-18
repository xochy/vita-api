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
     *
     * This method seeds the workouts table with data from a JSON file.
     * It deletes existing workouts, reads the JSON file, and processes each workout
     * to create a new workout entry in the database along with its relationships.
     *
     * @return void
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

    /**
     * Delete existing workouts from the database.
     *
     * @return void
     */
    private function deleteExistingWorkouts(): void
    {
        DB::table('workouts')->delete();
    }

    /**
     * Get the workouts data from the JSON file.
     *
     * @return array
     */
    private function getWorkoutsFromJson(): array
    {
        $workoutsJson = File::get(database_path('seeders/json/workouts.json'));
        return json_decode($workoutsJson, true);
    }

    /**
     * Process each workout data and create the workout with its relationships.
     *
     * @param array $workoutData
     *
     * @return void
     */
    private function processWorkout(array $workoutData): void
    {
        $muscles = $workoutData['muscles'];
        unset($workoutData['muscles']);

        $equipments = $workoutData['equipments'];
        unset($workoutData['equipments']);

        $translations = $workoutData['translations'];
        unset($workoutData['translations']);

        $category = $workoutData['category'];
        unset($workoutData['category']);

        $categoryId = $this->getCategoryIdByName($category);

        $workout = Workout::factory()->create(array_merge(
            $workoutData,
            ['category_id' => $categoryId],
            ['levels' => json_encode($workoutData['levels'] ?? [])]
        ));

        $this->attachMusclesToWorkout($workout, $muscles);
        $this->attachEquipmentsToWorkout($workout, $equipments);
        $this->handleTranslations($workout, $translations);
    }

    /**
     * Get the category ID by its name.
     *
     * @param string $categoryName
     *
     * @return int
     */
    private function getCategoryIdByName(string $categoryName): int
    {
        return DB::table('categories')
            ->where('name', $categoryName)
            ->value('id');
    }

    /**
     * Attach muscles to the workout.
     *
     * @param Workout $workout
     * @param array $muscles
     *
     * @return void
     */
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

    /**
     * Attach equipments to the workout.
     *
     * @param Workout $workout
     * @param array $equipments
     *
     * @return void
     */
    private function attachEquipmentsToWorkout(Workout $workout, array $equipments): void
    {
        foreach ($equipments as $equipment) {
            $equipmentId = DB::table('equipments')
                ->where('name', $equipment['name'])
                ->value('id');

            $workout->equipments()->attach($equipmentId);
        }
    }
}
