<?php

namespace Tests\Feature\Workouts;

use App\Models\Routine;
use App\Models\User;
use App\Models\Workout;
use Database\Seeders\permissionsSeeders\WorkoutsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IncludeRoutinesTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'workouts';
    const MODEL_INCLUDE_RELATIONSHIP_NAME = 'routine';
    const MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME = 'routines';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';

    const MODEL_RELATED_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME
        . '.' . self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME;

    const MODEL_SELF_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME
        . '.' . self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME . '.show';

    protected User $user;
    protected string $token;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(WorkoutsPermissionsSeeder::class);
        }

        [$this->user, $this->token] = $this->createUserWithToken();
    }

    /** @test */
    public function workouts_can_include_routines()
    {
        $workout = Workout::factory()->forCategory()
            ->hasAttached(
                Routine::factory(),
                ['series' => 3, 'repetitions' => 10, 'time' => 10, 'rest' => 60]
            )
            ->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME)
            ->withHeader('Authorization', $this->token)
            ->get(route(self::MODEL_MAIN_ACTION_ROUTE, $workout));

        $response->assertSee($workout->routines[0]->name);

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
    public function workouts_can_fetch_related_routines()
    {
        $workout = Workout::factory()->forCategory()
            ->hasAttached(
                Routine::factory(),
                ['series' => 3, 'repetitions' => 10, 'time' => 10, 'rest' => 60]
            )
            ->hasAttached(
                Routine::factory(),
                ['series' => 6, 'repetitions' => 12, 'time' => 15, 'rest' => 90]
            )
            ->hasAttached(
                Routine::factory(),
                ['series' => 9, 'repetitions' => 14, 'time' => 12, 'rest' => 75]
            )
            ->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME)
            ->withHeader('Authorization', $this->token)
            ->get(route(self::MODEL_MAIN_ACTION_ROUTE, $workout));

        $response->assertSee($workout->routines[0]->name);
        $response->assertSee($workout->routines[1]->name);
        $response->assertSee($workout->routines[2]->name);

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
    public function workout_can_include_routines_with_different_pivot_values()
    {
        $workout = Workout::factory()->forCategory()
            ->hasAttached(
                Routine::factory(),
                ['series' => 3, 'repetitions' => 10, 'time' => 10, 'rest' => 60]
            )
            ->hasAttached(
                Routine::factory(),
                ['series' => 6, 'repetitions' => 12, 'time' => 15, 'rest' => 90]
            )
            ->hasAttached(
                Routine::factory(),
                ['series' => 9, 'repetitions' => 14, 'time' => 12, 'rest' => 75]
            )
            ->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME)
            ->withHeader('Authorization', $this->token)
            ->get(route(self::MODEL_MAIN_ACTION_ROUTE, $workout));

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
            'routine_workout',
            [
                'workout_id' => $workout->id,
                'series' => 3,
                'repetitions' => 10,
                'time' => 10,
                'rest' => 60
            ]
        );

        $this->assertDatabaseHas(
            'routine_workout',
            [
                'workout_id' => $workout->id,
                'series' => 6,
                'repetitions' => 12,
                'time' => 15,
                'rest' => 90
            ]
        );

        $this->assertDatabaseHas(
            'routine_workout',
            [
                'workout_id' => $workout->id,
                'series' => 9,
                'repetitions' => 14,
                'time' => 12,
                'rest' => 75
            ]
        );
    }
}
