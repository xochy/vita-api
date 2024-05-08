<?php

namespace Tests\Feature\Routines;

use App\Models\Plan;
use App\Models\Routine;
use App\Models\User;
use Database\Seeders\permissionsSeeders\RoutinesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IncludePlansTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'routines';
    const MODEL_INCLUDE_RELATIONSHIP_NAME = 'plan';
    const MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME = 'plans';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';

    const MODEL_RELATED_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME
        . '.' . self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME;

    const MODEL_SELF_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME
        . '.' . self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME . '.show';

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(RoutinesPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function routines_can_include_plans()
    {
        $routine = Routine::factory()
            ->hasAttached(
                Plan::factory()->forGoal()->forFrequency()->forPhysicalCondition(),
            )
            ->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME)
            ->get(route(self::MODEL_MAIN_ACTION_ROUTE, $routine));

        $response->assertJsonFragment(
            [
                'related' => route(self::MODEL_RELATED_ROUTE, $routine)
            ]
        );

        $response->assertJsonFragment(
            [
                'self' => route(self::MODEL_SELF_ROUTE, $routine)
            ]
        );

        $this->assertDatabaseCount('plan_routine', 1);

        $this->assertDatabaseHas(
            'plan_routine',
            [
                'plan_id'    => $routine->plans[0]->id,
                'routine_id' => $routine->id
            ]
        );
    }

    /** @test */
    public function routines_can_fetch_related_plans()
    {
        $routine = Routine::factory()
            ->hasAttached(
                Plan::factory()->forGoal()->forFrequency()->forPhysicalCondition(),
            )
            ->hasAttached(
                Plan::factory()->forGoal()->forFrequency()->forPhysicalCondition(),
            )
            ->hasAttached(
                Plan::factory()->forGoal()->forFrequency()->forPhysicalCondition(),
            )
            ->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME)
            ->get(route(self::MODEL_MAIN_ACTION_ROUTE, $routine));

        $response->assertSee($routine->plans[0]->name);
        $response->assertSee($routine->plans[1]->name);
        $response->assertSee($routine->plans[2]->name);

        $response->assertJsonFragment(
            [
                'related' => route(self::MODEL_RELATED_ROUTE, $routine)
            ]
        );

        $response->assertJsonFragment(
            [
                'self' => route(self::MODEL_SELF_ROUTE, $routine)
            ]
        );

        $this->assertDatabaseCount('plan_routine', 3);

        $this->assertDatabaseHas(
            'plan_routine',
            [
                'plan_id'    => $routine->plans[0]->id,
                'routine_id' => $routine->id
            ]
        );

        $this->assertDatabaseHas(
            'plan_routine',
            [
                'plan_id'    => $routine->plans[1]->id,
                'routine_id' => $routine->id
            ]
        );

        $this->assertDatabaseHas(
            'plan_routine',
            [
                'plan_id'    => $routine->plans[2]->id,
                'routine_id' => $routine->id
            ]
        );
    }
}
