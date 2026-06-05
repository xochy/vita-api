<?php

namespace App\Services;

use App\Models\Routine;
use App\Models\Workout;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use LaravelJsonApi\Core\Exceptions\JsonApiException;

class RoutineGeneratorService
{
    /**
     * Finds workouts that match the given criteria and returns a collection of Workout models.
     *
     *  @param string $level The fitness level (e.g., 'beginner', 'intermediate', 'advanced').
     *  @param array $equipmentIds The IDs of the equipment to include.
     *  @param array $muscleIds The IDs of the muscles to target.
     *  @return Collection A collection of Workout models that match the criteria.
     */
    public function propose(string $level, array $equipmentIds, array $muscleIds): Collection
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

    /**
     * Finds workouts based on the provided criteria.
     *
     * @param string $user_id The ID of the user for whom the routine is being generated.
     * @param string $name The name of the routine.
     * @param string $gender The gender of the user (e.g., 'male', 'female').
     * @param int $age The age of the user.
     * @param string $goal The fitness goal of the user (e.g., 'lose weight', 'gain muscle').
     * @param array $workoutIds The IDs of the workouts to include in the routine.
     * @return Routine The generated routine with associated workouts and parameters.
     */
    public function generate($user_id, $name, string $gender, int $age, string $goal, $workoutIds): Routine
    {
        // Validate the workoutIds must be an array and not empty
        if (!is_array($workoutIds) || empty($workoutIds)) {
            throw JsonApiException::error(
                [
                    'status' => 400, // Bad request
                    'detail' => 'Invalid workout IDs provided.',
                ]
            );
        }

        // Validate that the workout IDs exist
        $workouts = Workout::whereIn('id', $workoutIds)->get();
        if ($workouts->isEmpty()) {
            throw JsonApiException::error(
                [
                    'status' => 404, // Not found
                    'detail' => 'No workouts found for the provided IDs.',
                ]
            );
        }

        // Create the routine and associate the workouts with dynamic parameters
        DB::beginTransaction();
        try {
            $routine = Routine::create([
                'name' => $name,
            ]);

            foreach ($workouts as $workout) {
                $parameters = $this->generateWorkoutParameters($gender, $age, $goal);
                $routine->workouts()->attach($workout->id, $parameters);
            }

            // Associate the routine with the user
            $routine->users()->attach($user_id);

            DB::commit();
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

    /**
     * Generates dynamic parameters for each workout based on gender, age, and goal
     *
     * @param string $gender The gender of the user (e.g., 'male', 'female').
     * @param int $age The age of the user.
     * @param string $goal The fitness goal of the user (e.g., 'lose weight', 'gain muscle').
     * @return array An array of parameters for the workout.
     */
    private function generateWorkoutParameters(string $gender, int $age, string $goal): array
    {
        $baseParameters = $this->getBaseParametersByGoal($goal);
        $genderModifiers = $this->getGenderModifiers($gender);
        $ageModifiers = $this->getAgeModifiers($age);

        // Apply modifiers
        return $this->applyModifiers($baseParameters, $genderModifiers, $ageModifiers);
    }

    /**
     * Get base parameters based on the user's fitness goal.
     *
     * @param string $goal The fitness goal of the user (e.g., 'lose weight', 'gain muscle').
     * @return array An array of base parameters for the specified goal.
     */
    private function getBaseParametersByGoal(string $goal): array
    {
        switch ($goal) {
            case 'lose weight':
                return [
                    'series' => [3, 4],          // Series range
                    'repetitions' => [12, 20],        // Higher repetitions range
                    'rest_seconds' => [30, 90],        // Shorter rests range
                    'intensity' => 'moderate-high'
                ];

            case 'gain strength':
                return [
                    'series' => [4, 6],       // More series
                    'repetitions' => [3, 6],       // Fewer repetitions
                    'rest_seconds' => [180, 300],   // Longer rests
                    'intensity' => 'high'
                ];

            case 'gain muscle':
            default:
                return [
                    'series' => [3, 4],          // Moderate series
                    'repetitions' => [8, 12],         // Hypertrophy range
                    'rest_seconds' => [60, 120],       // Moderate rest
                    'intensity' => 'moderate-high'
                ];
        }
    }

    /**
     * Get modifiers based on the user's gender
     *
     * @param string $gender The gender of the user (e.g., 'male', 'female').
     * @return array An array of modifiers based on the user's gender.
     */
    private function getGenderModifiers(string $gender): array
    {
        switch ($gender) {
            case 'female':
                return [
                    'series_multiplier' => 1.2, // 20% more series
                    'repetitions_multiplier' => 1.15, // 15% more repetitions
                    'rest_multiplier' => 0.8, // 20% less rest
                    'volume_increase' => true
                ];

            case 'male':
            default:
                return [
                    'series_multiplier' => 1.0,
                    'repetitions_multiplier' => 1.0,
                    'rest_multiplier' => 1.0,
                    'volume_increase' => false
                ];
        }
    }

    /**
     * Get age-specific modifiers
     *
     * @param int $age The age of the user.
     * @return array An array of modifiers based on the user's age.
     */
    private function getAgeModifiers(int $age): array
    {
        if ($age >= 50) {
            // Older adults: more rest, less intensity
            return [
                'series_multiplier' => 0.9,
                'repetitions_adjustment' => 2, // +2 repetitions for lower load
                'rest_multiplier' => 1.3,
                'intensity_reduction' => true
            ];
        } elseif ($age >= 35) {
            // Adults: slight increase in rest
            return [
                'series_multiplier' => 1.0,
                'repetitions_adjustment' => 0,
                'rest_multiplier' => 1.1,
                'intensity_reduction' => false
            ];
        } else {
            // Youth: standard parameters
            return [
                'series_multiplier' => 1.0,
                'repetitions_adjustment' => 0,
                'rest_multiplier' => 1.0,
                'intensity_reduction' => false
            ];
        }
    }

    /**
     * Apply all modifiers and generate the final parameters
     *
     * @param array $base The base parameters for the workout.
     * @param array $genderMod
     * @param array $ageMod
     * @return array An array of final parameters for the workout.
     */
    private function applyModifiers(array $base, array $genderMod, array $ageMod): array
    {
        // Calculate series
        $seriesMin = max(
            1,
            round(
                $base['series'][0] * $genderMod['series_multiplier']
                * $ageMod['series_multiplier']
            )
        );
        $seriesMax = max(
            $seriesMin,
            round(
                $base['series'][1] * $genderMod['series_multiplier']
                * $ageMod['series_multiplier']
            )
        );

        // Calculate repetitions
        $repMin = max(
            1,
            round(
                $base['repetitions'][0] * $genderMod['repetitions_multiplier']
            ) + $ageMod['repetitions_adjustment']
        );
        $repMax = max(
            $repMin,
            round(
                $base['repetitions'][1] * $genderMod['repetitions_multiplier']
            ) + $ageMod['repetitions_adjustment']
        );

        // Calculate rest
        $restMin = max(
            15,
            round(
                $base['rest_seconds'][0] * $genderMod['rest_multiplier'] * $ageMod['rest_multiplier']
            )
        );
        $restMax = max(
            $restMin,
            round(
                $base['rest_seconds'][1] * $genderMod['rest_multiplier'] * $ageMod['rest_multiplier']
            )
        );

        // Generate estimated time per set (based on repetitions and tempo)
        $timePerRep = $this->estimateTimePerRepetition($base['intensity']);

        $timeMin = round($repMin * $timePerRep); // Keep in seconds as integer
        $timeMax = round($repMax * $timePerRep);

        return [
            'series' => $this->formatRange($seriesMin, $seriesMax),
            'repetitions' => $this->formatRange($repMin, $repMax),
            'time' => $this->formatTime($timeMin, $timeMax),
            'rest' => $this->formatTime($restMin, $restMax)
        ];
    }

    /**
     * Estimate time per repetition based on exercise type and intensity
     *
     * @param string $intensity The intensity of the workout (e.g., 'high', 'moderate-high').
     * @return float Estimated time in seconds per repetition.
     */
    private function estimateTimePerRepetition(string $intensity): float
    {
        // Base time in seconds per repetition
        $baseTime = 3.0; // 3 seconds per repetition (standard tempo)

        // Adjust based on intensity
        switch ($intensity) {
            case 'high':
                return $baseTime * 1.3; // More time for heavy exercises
            case 'moderate-high':
                return $baseTime * 1.1;
            default:
                return $baseTime;
        }
    }

    /**
     * Format a numeric range
     *
     * @param int $min Minimum value of the range.
     * @param int $max Maximum value of the range.
     * @return string A formatted string representing the range.
     */
    private function formatRange(int $min, int $max): string
    {
        return $min === $max ? (string) $min : "{$min}-{$max}";
    }

    /**
     * Format a time range
     *
     * @param int $min Minimum time in minutes.
     * @param int $max Maximum time in minutes.
     * @return string A formatted string representing the time range.
     */
    private function formatTime(int $min, int $max): string
    {
        $minStr = $min >= 60 ? round($min / 60) : $min;
        $maxStr = $max >= 60 ? round($max / 60) . ' min' : $max . ' seg';

        return $min === $max ? $minStr : "{$minStr}-{$maxStr}";
    }

    /**
     * Find matching workouts based on user preferences
     *
     * @param string $level The fitness level (e.g., 'beginner', 'intermediate', 'advanced').
     * @param array $equipmentIds The IDs of the equipment to include.
     * @param array $muscleIds The IDs of the muscles to target.
     * @return Collection A collection of Workout models that match the criteria.
     */
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
        return $query->inRandomOrder()->limit(7)->get();
    }
}
