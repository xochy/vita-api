<?php

namespace Tests\Feature\Workouts;

use App\Enums\MusclePriorityEnum;
use App\Models\Muscle;
use App\Models\Category;
use App\Models\User;
use App\Models\Workout;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IncludeMusclesTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'workouts';
    const MODEL_INCLUDE_RELATIONSHIP_NAME = 'muscle';
    const MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME = 'muscles';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';

    const MODEL_RELATED_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME
        . '.' . self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME;

    const MODEL_SELF_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME
        . '.' . self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME . '.show';

    protected User $user;
    protected Category $category;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
        $this->category = Category::factory()->create();
    }

    /** @test */
    public function workouts_can_include_muscles()
    {
        $workout = Workout::factory()
            ->for($this->category)
            ->hasAttached(
                Muscle::factory(),
                [
                    'priority' => MusclePriorityEnum::PRINCIPAL
                ]
            )
            ->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME)
            ->get(route(self::MODEL_MAIN_ACTION_ROUTE, $workout));

        $response->assertSee($workout->muscles[0]->name);

        $response->assertJsonFragment(
            [
                'related' => route(self::MODEL_RELATED_ROUTE, $workout)
            ]
        );

        $response->assertJsonFragment(
            [
                'self' => route(self::MODEL_SELF_ROUTE, $workout)
            ]
        );

        $this->assertDatabaseHas(
            'muscle_workout',
            [
                'workout_id' => $workout->id,
                'priority' => MusclePriorityEnum::PRINCIPAL
            ]
        );
    }

    /** @test */
    public function workouts_can_fetch_related_muscles()
    {
        $workout = Workout::factory()
            ->for($this->category)
            ->hasAttached(
                Muscle::factory(),
                [
                    'priority' => MusclePriorityEnum::PRINCIPAL
                ]
            )
            ->hasAttached(
                Muscle::factory(),
                [
                    'priority' => MusclePriorityEnum::SECONDARY
                ]
            )
            ->hasAttached(
                Muscle::factory(),
                [
                    'priority' => MusclePriorityEnum::ANTAGONIST
                ]
            )
            ->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME)
            ->get(route(self::MODEL_MAIN_ACTION_ROUTE, $workout));

        $response->assertSee($workout->muscles[0]->name);
        $response->assertSee($workout->muscles[1]->name);
        $response->assertSee($workout->muscles[2]->name);

        $response->assertJsonFragment(
            [
                'related' => route(self::MODEL_RELATED_ROUTE, $workout)
            ]
        );

        $response->assertJsonFragment(
            [
                'self' => route(self::MODEL_SELF_ROUTE, $workout)
            ]
        );
    }

    /** @test */
    public function workouts_can_include_muscles_with_different_priorities()
    {
        $workout = Workout::factory()
            ->for($this->category)
            ->hasAttached(
                Muscle::factory(),
                [
                    'priority' => MusclePriorityEnum::PRINCIPAL
                ]
            )
            ->hasAttached(
                Muscle::factory(),
                [
                    'priority' => MusclePriorityEnum::SECONDARY
                ]
            )
            ->hasAttached(
                Muscle::factory(),
                [
                    'priority' => MusclePriorityEnum::ANTAGONIST
                ]
            )
            ->create();

        $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME)
            ->get(route(self::MODEL_MAIN_ACTION_ROUTE, $workout));

        $this->assertDatabaseHas(
            'muscle_workout',
            [
                'workout_id' => $workout->id,
                'muscle_id' => $workout->muscles[0]->id,
                'priority' => MusclePriorityEnum::PRINCIPAL
            ]
        );

        $this->assertDatabaseHas(
            'muscle_workout',
            [
                'workout_id' => $workout->id,
                'muscle_id' => $workout->muscles[1]->id,
                'priority' => MusclePriorityEnum::SECONDARY
            ]
        );

        $this->assertDatabaseHas(
            'muscle_workout',
            [
                'workout_id' => $workout->id,
                'muscle_id' => $workout->muscles[2]->id,
                'priority' => MusclePriorityEnum::ANTAGONIST
            ]
        );
    }
}
