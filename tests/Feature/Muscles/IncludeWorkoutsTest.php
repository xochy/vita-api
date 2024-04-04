<?php

namespace Tests\Feature\Muscles;

use App\Enums\MusclePriorityEnum;
use App\Models\Muscle;
use App\Models\Subcategory;
use App\Models\User;
use App\Models\Workout;
use Database\Seeders\permissionsSeeders\WorkoutsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IncludeWorkoutsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'muscles';
    const MODEL_INCLUDE_RELATIONSHIP_NAME = 'workout';
    const MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME = 'workouts';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';

    const MODEL_RELATED_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME
        . '.' . self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME;

    const MODEL_SELF_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME
        . '.' . self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME . '.show';

    protected User $user;
    protected Subcategory $subcategory;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(WorkoutsPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
        $this->subcategory = Subcategory::factory()->forCategory()->create();
    }

    /** @test */
    public function muscles_can_include_workouts()
    {
        $muscle = Muscle::factory()
            ->hasAttached(
                Workout::factory()->for($this->subcategory),
                ['priority' => MusclePriorityEnum::PRINCIPAL]
            )
            ->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME)
            ->get(route(self::MODEL_MAIN_ACTION_ROUTE, $muscle));

        $response->assertSee($muscle->workouts[0]->name);

        $response->assertJsonFragment([
            'related' => route(self::MODEL_RELATED_ROUTE, $muscle)
        ]);

        $response->assertJsonFragment([
            'self' => route(self::MODEL_SELF_ROUTE, $muscle)
        ]);

        $this->assertDatabaseHas('muscle_workout', [
            'muscle_id' => $muscle->id,
            'priority' => MusclePriorityEnum::PRINCIPAL
        ]);
    }

    /** @test */
    public function muscles_can_fetch_related_workouts()
    {
        $muscle = Muscle::factory()
            ->hasAttached(
                Workout::factory()->for($this->subcategory),
                ['priority' => MusclePriorityEnum::PRINCIPAL]
            )
            ->hasAttached(
                Workout::factory()->for($this->subcategory),
                ['priority' => MusclePriorityEnum::PRINCIPAL]
            )
            ->hasAttached(
                Workout::factory()->for($this->subcategory),
                ['priority' => MusclePriorityEnum::SECONDARY]
            )
            ->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME)
            ->get(route(self::MODEL_MAIN_ACTION_ROUTE, $muscle));

        $response->assertSee($muscle->workouts[0]->name);
        $response->assertSee($muscle->workouts[1]->name);
        $response->assertSee($muscle->workouts[2]->name);

        $response->assertJsonFragment([
            'related' => route(self::MODEL_RELATED_ROUTE, $muscle)
        ]);

        $response->assertJsonFragment([
            'self' => route(self::MODEL_SELF_ROUTE, $muscle)
        ]);
    }

    /** @test */
    public function muscles_can_include_workouts_with_different_priorities()
    {
        $muscle = Muscle::factory()
            ->hasAttached(
                Workout::factory()->for($this->subcategory),
                ['priority' => MusclePriorityEnum::PRINCIPAL]
            )
            ->hasAttached(
                Workout::factory()->for($this->subcategory),
                ['priority' => MusclePriorityEnum::SECONDARY]
            )
            ->hasAttached(
                Workout::factory()->for($this->subcategory),
                ['priority' => MusclePriorityEnum::ANTAGONIST]
            )
            ->create();

        $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME)
            ->get(route(self::MODEL_MAIN_ACTION_ROUTE, $muscle));

        $this->assertDatabaseHas('muscle_workout', [
            'workout_id' => $muscle->workouts[0]->id,
            'muscle_id' => $muscle->id,
            'priority' => MusclePriorityEnum::PRINCIPAL
        ]);

        $this->assertDatabaseHas('muscle_workout', [
            'workout_id' => $muscle->workouts[1]->id,
            'muscle_id' => $muscle->id,
            'priority' => MusclePriorityEnum::SECONDARY
        ]);

        $this->assertDatabaseHas('muscle_workout', [
            'workout_id' => $muscle->workouts[2]->id,
            'muscle_id' => $muscle->id,
            'priority' => MusclePriorityEnum::ANTAGONIST
        ]);
    }
}
