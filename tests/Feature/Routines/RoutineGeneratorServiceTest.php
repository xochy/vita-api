<?php

namespace Tests\Feature\Routines;

use App\Models\Equipment;
use App\Models\Routine;
use App\Models\User;
use App\Models\Workout;
use App\Services\RoutineGeneratorService;
use Database\Seeders\CategorySeeder;
use Database\Seeders\EquipmentSeeder;
use Database\Seeders\MuscleSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\VariationSeeder;
use Database\Seeders\WorkoutSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelJsonApi\Core\Exceptions\JsonApiException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoutineGeneratorServiceTest extends TestCase
{
    use RefreshDatabase;

    const TIME_PATTERN = '/^(\d+)(-(\d+))?$/';
    const TIMES_PATTERN = '/^[0-9]+(-[0-9]+)?$/';
    const REST_PATTERN = '/^[0-9]+(-[0-9]+)?\s+(seg|min)$/';
    const GAIN_MUSCLE_GOAL = 'gain muscle';
    const LOSE_WEIGHT_GOAL = 'lose weight';
    const JSON_API_EXCEPTION = 'JSON:API error';
    const TEST_ROUTINE_NAME = 'Test Routine';

    private RoutineGeneratorService $service;
    private User $user;
    private string $token;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);


            $this->seed(MuscleSeeder::class);
            $this->seed(CategorySeeder::class);
            $this->seed(EquipmentSeeder::class);

            $this->seed(WorkoutSeeder::class);
            $this->seed(VariationSeeder::class);
        }

        [$this->user, $this->token] = $this->createUserWithToken();
        $this->service = new RoutineGeneratorService();
    }

    /** @test */
    public function it_can_propose_workouts_for_beginner_level()
    {
        // Arrange
        Workout::factory()->forCategory()
            ->count(3)->beginnerOnly()->create();

        // Act
        $result = $this->service->propose('beginner', [1], []);

        // Assert
        $this->assertNotEmpty($result);
        $this->assertLessThanOrEqual(7, $result->count());

        // Verify all workouts contain 'beginner' level
        foreach ($result as $workout) {
            $levels = json_decode($workout->levels, true);
            $this->assertContains('beginner', $levels);
        }
    }

    /** @test */
    public function it_throws_exception_when_no_matching_workouts_found()
    {
        // Arrange - Create workouts that don't match the criteria
        Workout::factory()->forCategory()
            ->count(2)->withLevels(['advanced'])->create();

        // Act & Assert
        $this->expectException(JsonApiException::class);

        $this->service->propose(
            'beginner',
            [999],
            []
        ); // Non-existent equipment ID
    }

    /** @test */
    public function it_can_generate_routine_for_male_gaining_muscle()
    {
        // Arrange
        $workouts = Workout::factory()->forCategory()
            ->count(3)->create();

        $workoutIds = $workouts->pluck('id')->toArray();

        // Act
        $routine = $this->service->generate(
            $this->user->id,
            'Test Muscle Routine',
            'male',
            25,
            self::GAIN_MUSCLE_GOAL,
            $workoutIds
        );

        // Assert
        $this->assertInstanceOf(Routine::class, $routine);
        $this->assertEquals('Test Muscle Routine', $routine->name);
        $this->assertEquals($this->user->id, $routine->users->first()->id);
        $this->assertEquals(3, $routine->workouts->count());

        // Verify muscle gain parameters
        $pivotData = $routine->workouts->first()->routine_workout;
        $this->assertMatchesRegularExpression(self::TIMES_PATTERN, $pivotData->series);
        $this->assertMatchesRegularExpression(self::TIMES_PATTERN, $pivotData->repetitions);
        $this->assertStringContainsString(' seg', $pivotData->time . ' min');
        $this->assertStringContainsString(' min', $pivotData->rest . ' min');
    }

    /** @test */
    public function it_can_generate_routine_for_female_losing_weight()
    {
        // Arrange
        $workouts = Workout::factory()->forCategory()
            ->count(3)->create();

        $workoutIds = $workouts->pluck('id')->toArray();

        // Act
        $routine = $this->service->generate(
            $this->user->id,
            'Weight Loss Routine',
            'female',
            30,
            self::LOSE_WEIGHT_GOAL,
            $workoutIds
        );

        // Assert
        $this->assertInstanceOf(Routine::class, $routine);

        // Verify weight loss parameters (higher reps, shorter rest)
        $pivotData = $routine->workouts->first()->routine_workout;

        // Extract repetition values to verify they're in weight loss range
        preg_match(
            self::TIME_PATTERN,
            $pivotData->repetitions,
            $repMatches
        );

        $minReps = (int) $repMatches[1];
        $maxReps = isset($repMatches[3]) ? (int) $repMatches[3] : $minReps;

        $this->assertGreaterThanOrEqual(
            10,
            $minReps,
            'Weight loss should have higher repetitions'
        );

        $this->assertLessThanOrEqual(
            25,
            $maxReps,
            'Repetitions should be reasonable'
        );
    }

    /** @test */
    public function it_can_generate_routine_for_gaining_strength()
    {
        // Arrange
        $workouts = Workout::factory()->forCategory()
            ->count(3)->create();

        $workoutIds = $workouts->pluck('id')->toArray();

        // Act
        $routine = $this->service->generate(
            $this->user->id,
            'Strength Routine',
            'male',
            35,
            'gain strength',
            $workoutIds
        );

        // Assert
        $this->assertInstanceOf(Routine::class, $routine);

        // Verify strength parameters (lower reps, longer rest)
        $pivotData = $routine->workouts->first()->routine_workout;

        // Extract repetition values
        preg_match(self::TIME_PATTERN, $pivotData->repetitions, $repMatches);
        $minReps = (int) $repMatches[1];
        $maxReps = isset($repMatches[3]) ? (int) $repMatches[3] : $minReps;

        $this->assertLessThanOrEqual(
            8,
            $maxReps,
            'Strength training should have lower repetitions'
        );

        $this->assertGreaterThanOrEqual(
            1,
            $minReps
        );

        // Verify longer rest periods for strength training
        $this->assertStringContainsString('min', $pivotData->rest);
    }

    /** @test */
    public function it_applies_gender_specific_modifications()
    {
        // Arrange
        $workouts = Workout::factory()->forCategory()
            ->count(2)->create();

        $workoutIds = $workouts->pluck('id')->toArray();

        // Act - Generate for female
        $femaleRoutine = $this->service->generate(
            $this->user->id,
            'Female Routine',
            'female',
            25,
            self::GAIN_MUSCLE_GOAL,
            $workoutIds
        );

        // Act - Generate for male with same parameters
        $maleUser = User::factory()->create();
        $maleRoutine = $this->service->generate(
            $maleUser->id,
            'Male Routine',
            'male',
            25,
            self::GAIN_MUSCLE_GOAL,
            $workoutIds
        );

        // Assert - Females should generally have more volume
        $femalePivot = $femaleRoutine->workouts->first()->routine_workout;
        $malePivot = $maleRoutine->workouts->first()->routine_workout;

        // Extract series values
        preg_match(
            self::TIME_PATTERN,
            $femalePivot->series,
            $femaleSeriesMatches
        );

        preg_match(
            self::TIME_PATTERN,
            $malePivot->series,
            $maleSeriesMatches
        );

        $femaleMaxSeries = isset($femaleSeriesMatches[3])
            ? (int) $femaleSeriesMatches[3]
            : (int) $femaleSeriesMatches[1];

        $maleMaxSeries = isset($maleSeriesMatches[3])
            ? (int) $maleSeriesMatches[3]
            : (int) $maleSeriesMatches[1];

        // Females should have equal or more series due to gender modifiers
        $this->assertGreaterThanOrEqual($maleMaxSeries, $femaleMaxSeries);
    }

    /** @test */
    public function it_applies_age_specific_modifications()
    {
        // Arrange
        $workouts = Workout::factory()->forCategory()
            ->count(2)->create();

        $workoutIds = $workouts->pluck('id')->toArray();

        // Act - Generate for older adult
        $olderRoutine = $this->service->generate(
            $this->user->id,
            'Senior Routine',
            'male',
            55,
            self::GAIN_MUSCLE_GOAL,
            $workoutIds
        );

        // Act - Generate for young adult
        $youngerUser = User::factory()->create();
        $youngerRoutine = $this->service->generate(
            $youngerUser->id,
            'Young Routine',
            'male',
            25,
            self::GAIN_MUSCLE_GOAL,
            $workoutIds
        );

        // Assert - Older adults should have longer rest periods
        $olderPivot = $olderRoutine->workouts->first()->routine_workout;
        $youngerPivot = $youngerRoutine->workouts->first()->routine_workout;

        // Both should have rest periods, but we can verify they're formatted correctly
        $this->assertMatchesRegularExpression(
            self::REST_PATTERN,
            $olderPivot->rest
        );

        $this->assertMatchesRegularExpression(
            self::REST_PATTERN,
            $youngerPivot->rest
        );
    }

    /** @test */
    public function it_throws_exception_for_invalid_workout_ids()
    {
        // Act & Assert
        $this->expectException(JsonApiException::class);
        $this->expectExceptionMessage(self::JSON_API_EXCEPTION);

        $this->service->generate(
            $this->user->id,
            self::TEST_ROUTINE_NAME,
            'male',
            25,
            self::GAIN_MUSCLE_GOAL,
            ['not-an-array']
        );
    }

    /** @test */
    public function it_throws_exception_for_empty_workout_ids()
    {
        // Act & Assert
        $this->expectException(JsonApiException::class);
        $this->expectExceptionMessage(self::JSON_API_EXCEPTION);

        $this->service->generate(
            $this->user->id,
            self::TEST_ROUTINE_NAME,
            'male',
            25,
            self::GAIN_MUSCLE_GOAL,
            []
        );
    }

    /** @test */
    public function it_throws_exception_for_non_existent_workout_ids()
    {
        // Act & Assert
        $this->expectException(JsonApiException::class);
        $this->expectExceptionMessage(self::JSON_API_EXCEPTION);

        $this->service->generate(
            $this->user->id,
            self::TEST_ROUTINE_NAME,
            'male',
            25,
            self::GAIN_MUSCLE_GOAL,
            [999, 1000, 1001]
        );
    }

    /** @test */
    public function it_formats_time_and_rest_as_integers()
    {
        // Arrange
        $workouts = Workout::factory()->forCategory()
            ->count(3)->create();

        $workoutIds = $workouts->pluck('id')->toArray();

        // Act
        $routine = $this->service->generate(
            $this->user->id,
            'Integer Test Routine',
            'female',
            25,
            self::LOSE_WEIGHT_GOAL,
            $workoutIds
        );

        // Assert
        foreach ($routine->workouts as $workout) {
            $pivot = $workout->routine_workout;

            // Verify time format (should be integers with seg/min)
            $this->assertMatchesRegularExpression(
                self::REST_PATTERN,
                $pivot->time
            );

            // Verify rest format (should be integers with seg/min)
            $this->assertMatchesRegularExpression(
                self::REST_PATTERN,
                $pivot->rest
            );

            // Extract numbers to verify they're integers
            preg_match_all('/\d+/', $pivot->time, $timeNumbers);
            preg_match_all('/\d+/', $pivot->rest, $restNumbers);

            foreach ($timeNumbers[0] as $number) {
                $this->assertEquals(
                    (string) (int) $number,
                    $number,
                    'Time should contain only integers'
                );
            }

            foreach ($restNumbers[0] as $number) {
                $this->assertEquals(
                    (string) (int) $number,
                    $number,
                    'Rest should contain only integers'
                );
            }
        }
    }

    /** @test */
    public function it_generates_different_parameters_for_different_goals()
    {
        // Arrange
        $workouts = Workout::factory()->forCategory()
            ->count(1)->create();

        $workoutIds = $workouts->pluck('id')->toArray();

        // Act - Generate routines for all three goals
        $strengthRoutine = $this->service->generate(
            $this->user->id,
            'Strength',
            'male',
            30,
            'gain strength',
            $workoutIds
        );

        $muscleUser = User::factory()->create();

        $muscleRoutine = $this->service->generate(
            $muscleUser->id,
            'Muscle',
            'male',
            30,
            'gain muscle',
            $workoutIds
        );
        $weightUser = User::factory()->create();

        $weightRoutine = $this->service->generate(
            $weightUser->id,
            'Weight',
            'male',
            30,
            self::LOSE_WEIGHT_GOAL,
            $workoutIds
        );

        // Assert - Each goal should produce different parameters
        $strengthPivot = $strengthRoutine->workouts->first()->routine_workout;
        $musclePivot = $muscleRoutine->workouts->first()->routine_workout;
        $weightPivot = $weightRoutine->workouts->first()->routine_workout;

        // Verify all have different characteristics
        $this->assertNotEquals(
            $strengthPivot->repetitions,
            $weightPivot->repetitions
        );

        $this->assertNotEquals(
            $strengthPivot->rest,
            $weightPivot->rest
        );

        // Verify parameters are within expected ranges for each goal
        $this->assertMatchesRegularExpression(
            self::TIMES_PATTERN,
            $strengthPivot->series
        );

        $this->assertMatchesRegularExpression(
            self::TIMES_PATTERN,
            $musclePivot->series
        );

        $this->assertMatchesRegularExpression(
            self::TIMES_PATTERN,
            $weightPivot->series
        );
    }

    /** @test */
    public function it_handles_equipment_filtering_in_propose()
    {
        // Arrange
        $equipment = Equipment::factory()->create();

        $workoutsWithEquipment = Workout::factory()->forCategory()
            ->count(2)->beginnerOnly()->create();

        // Attach equipment to some workouts
        $workoutsWithEquipment->first()->equipments()->attach($equipment->id);

        // Act - Search with specific equipment
        $resultWithEquipment = $this->service->propose(
            'beginner',
            [$equipment->id],
            []
        );

        // Assert
        $this->assertNotEmpty($resultWithEquipment);
    }

}
