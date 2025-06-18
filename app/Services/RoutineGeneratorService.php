<?php

namespace App\Services;

use App\Models\Routine;
use App\Models\Workout;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LaravelJsonApi\Core\Exceptions\JsonApiException;

class RoutineGeneratorService
{
    public function propose(int $userId, string $gender, int $age, string $goal, string $level, array $equipmentIds, array $muscleIds): Collection
    {
        $workouts = $this->findMatchingWorkouts($level, $equipmentIds, $muscleIds);

        if ($workouts->isEmpty()) {
            throw JsonApiException::error(
                [
                    'status' => 400, // Wrong request
                    'detail' => 'Model not found.',
                ]
            );
        }

        return $workouts;
    }

    public function generate($name,$workoutIds): Routine
    {
        // Validar que workoutIds sea un array
        if (!is_array($workoutIds) || empty($workoutIds)) {
            throw JsonApiException::error(
                [
                    'status' => 400, // Bad request
                    'detail' => 'Invalid workout IDs provided.',
                ]
            );
        }

        // Validar que los IDs de los workouts existan
        $workouts = Workout::whereIn('id', $workoutIds)->get();
        if ($workouts->isEmpty()) {
            throw JsonApiException::error(
                [
                    'status' => 404, // Not found
                    'detail' => 'No workouts found for the provided IDs.',
                ]
            );
        }

        // 2. Crear la rutina y asociar los ejercicios
        DB::beginTransaction();
        try {
            $routine = Routine::create([
                'name' => $name,
            ]);

            foreach ($workouts as $workout) {
                $routine->workouts()->attach($workout->id, [
                    'series' => '3-5',
                    'repetitions' => '8-12',
                    'time' => '45-60 seg',
                    'rest' => '2-3 min'
                ]);
            }

            DB::commit();
            // return $routine->load('workouts')->toArray();
            return $routine;
        } catch (\Exception $e) {
            DB::rollBack();
            throw JsonApiException::error(
                [
                    'status' => 500, // Internal server error
                    'detail' => 'Failed to generate routine: ' . $e->getMessage(),
                ]
            );
        }
    }

    private function findMatchingWorkouts(string $level, array $equipmentIds, array $muscleIds): Collection
    {
        $query = Workout::query();

        // Filter by level considering the 'levels' attribute is a json array
        $query->whereJsonContains('levels', $level);

        // Filter by muscles
        if (!empty($muscleIds)) {
            $query->whereHas('muscles', function ($q) use ($muscleIds) {
                $q->whereIn('muscles.id', $muscleIds)->where('muscle_workout.priority', 'Principal');
            });
        }

        //Filter by equipment
        if (!empty($equipmentIds)) {
            $query->whereHas('equipments', function ($q) use ($equipmentIds) {
                $q->whereIn('equipments.id', $equipmentIds);
            });
        } else {
            // Si el usuario no tiene equipo, solo mostrar los que no requieren.
            $query->whereDoesntHave('equipment');
        }

        // Limitar a un número razonable de ejercicios y ordenarlos aleatoriamente
        return $query->inRandomOrder()->limit(5)->get();
    }
}
