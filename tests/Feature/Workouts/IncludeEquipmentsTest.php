<?php

namespace Tests\Feature\Workouts;

use App\Models\Equipment;
use App\Models\User;
use App\Models\Workout;
use Database\Seeders\permissionsSeeders\WorkoutsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IncludeEquipmentsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'workouts';
    const MODEL_INCLUDE_RELATIONSHIP_NAME = 'equipment';
    const MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME = 'equipments';
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
    public function workouts_can_include_equipments()
    {
        $workout = Workout::factory()->forCategory()
            ->has(Equipment::factory()->count(2), 'equipments')
            ->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME)
            ->withHeader('Authorization', $this->token)
            ->get(route(self::MODEL_MAIN_ACTION_ROUTE, $workout));

        $response->assertSee($workout->equipments[0]->name);

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
            'equipment_workout',
            [
                'equipment_id' => $workout->equipments[0]->id,
                'workout_id' => $workout->id
            ]
        );
    }

    /** @test */
    public function workouts_can_fetch_related_equipments()
    {
        $workout = Workout::factory()->forCategory()
            ->has(Equipment::factory()->count(2), 'equipments')
            ->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME)
            ->withHeader('Authorization', $this->token)
            ->get(route(self::MODEL_MAIN_ACTION_ROUTE, $workout));

        $response->assertSee($workout->equipments[0]->name);

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
}
