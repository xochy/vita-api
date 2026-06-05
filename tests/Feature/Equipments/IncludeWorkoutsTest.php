<?php

namespace Tests\Feature\Equipments;

use App\Models\Equipment;
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

    const MODEL_PLURAL_NAME = 'equipments';
    const MODEL_INCLUDE_RELATIONSHIP_NAME = 'workout';
    const MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME = 'workouts';
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
    public function equipments_can_include_workouts()
    {
        $equipment = Equipment::factory()
            ->hasAttached(
                Workout::factory()->forCategory()
            )
            ->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME)
            ->withHeader('Authorization', $this->token)
            ->get(route(self::MODEL_MAIN_ACTION_ROUTE, $equipment));

        $response->assertSee($equipment->workouts[0]->name);

        $response->assertJsonFragment(
            [
                'related' => route(self::MODEL_RELATED_ROUTE, $equipment)
            ]
        );

        $response->assertJsonFragment(
            [
                'self' => route(self::MODEL_SELF_ROUTE, $equipment)
            ]
        );

        $this->assertDatabaseHas(
            'equipment_workout',
            [
                'equipment_id' => $equipment->id,
                'workout_id' => $equipment->workouts[0]->id
            ]
        );
    }

    /** @test */
    public function equipments_can_fetch_related_workouts()
    {
        $equipment = Equipment::factory()
            ->hasAttached(
                Workout::factory()->forCategory()
            )
            ->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME)
            ->withHeader('Authorization', $this->token)
            ->get(route(self::MODEL_MAIN_ACTION_ROUTE, $equipment));

        $response->assertSee($equipment->workouts[0]->name);

        $response->assertJsonFragment(
            [
                'self' => route(self::MODEL_SELF_ROUTE, $equipment)
            ]
        );
        $response->assertJsonFragment(
            [
                'related' => route(self::MODEL_RELATED_ROUTE, $equipment)
            ]
        );
    }
}
