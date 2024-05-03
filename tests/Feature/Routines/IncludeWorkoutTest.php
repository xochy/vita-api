<?php

namespace Tests\Feature\Routines;

use App\Models\Routine;
use App\Models\Subcategory;
use App\Models\User;
use App\Models\Workout;
use Database\Seeders\permissionsSeeders\RoutinesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IncludeWorkoutTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'routines';
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
            $this->seed(RoutinesPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
        $this->subcategory = Subcategory::factory()->forCategory()->create();
    }

    /** @test */
    public function routines_can_include_workouts()
    {
        $routine = Routine::factory()
            ->hasAttached(
                Workout::factory()->for($this->subcategory),
                ['series' => 3, 'repetitions' => 10, 'time' => 10]
            )
            ->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME)
            ->get(route(self::MODEL_MAIN_ACTION_ROUTE, $routine));

        $response->assertJsonFragment([
            'related' => route(self::MODEL_RELATED_ROUTE, $routine)
        ]);

        $response->assertJsonFragment([
            'self' => route(self::MODEL_SELF_ROUTE, $routine)
        ]);
    }

    /** @test */
    public function routines_can_fetch_related_workouts()
    {
        $routine = Routine::factory()
            ->hasAttached(
                Workout::factory()->for($this->subcategory),
                ['series' => 3, 'repetitions' => 10, 'time' => 10]
            )
            ->hasAttached(
                Workout::factory()->for($this->subcategory),
                ['series' => 5, 'repetitions' => 12, 'time' => 15]
            )
            ->hasAttached(
                Workout::factory()->for($this->subcategory),
                ['series' => 4, 'repetitions' => 8, 'time' => 12]
            )
            ->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME)
            ->get(route(self::MODEL_MAIN_ACTION_ROUTE, $routine));

        $response->assertSee($routine->workouts[0]->name);
        $response->assertSee($routine->workouts[1]->name);
        $response->assertSee($routine->workouts[2]->name);

        $response->assertJsonFragment([
            'related' => route(self::MODEL_RELATED_ROUTE, $routine)
        ]);

        $response->assertJsonFragment([
            'self' => route(self::MODEL_SELF_ROUTE, $routine)
        ]);
    }

    /** @test */
    public function routines_can_include_workouts_with_different_pivot_values()
    {
        $routine = Routine::factory()
            ->hasAttached(
                Workout::factory()->for($this->subcategory),
                ['series' => 3, 'repetitions' => 10, 'time' => 10]
            )
            ->hasAttached(
                Workout::factory()->for($this->subcategory),
                ['series' => 5, 'repetitions' => 12, 'time' => 15]
            )
            ->hasAttached(
                Workout::factory()->for($this->subcategory),
                ['series' => 4, 'repetitions' => 8, 'time' => 12]
            )
            ->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME)
            ->get(route(self::MODEL_MAIN_ACTION_ROUTE, $routine));

        $response->assertJsonFragment([
            'related' => route(self::MODEL_RELATED_ROUTE, $routine)
        ]);

        $response->assertJsonFragment([
            'self' => route(self::MODEL_SELF_ROUTE, $routine)
        ]);

        $this->assertDatabaseHas('routine_workout', [
            'routine_id'  => $routine->id,
            'series'      => 3,
            'repetitions' => 10,
            'time'        => 10
        ]);

        $this->assertDatabaseHas('routine_workout', [
            'routine_id'  => $routine->id,
            'series'      => 5,
            'repetitions' => 12,
            'time'        => 15
        ]);

        $this->assertDatabaseHas('routine_workout', [
            'routine_id'  => $routine->id,
            'series'      => 4,
            'repetitions' => 8,
            'time'        => 12
        ]);
    }
}
