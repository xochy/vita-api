<?php

namespace Tests\Feature\Plans;

use App\Models\Plan;
use App\Models\User;
use Database\Seeders\permissionsSeeders\PhysicalConditionsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IncludePhysicalConditionTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'plans';
    const MODEL_INCLUDE_RELATIONSHIP_NAME = 'physicalCondition';
    const MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME = 'physical-conditions';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';

    const MODEL_RELATED_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME
        . '.' . self::MODEL_INCLUDE_RELATIONSHIP_NAME;

    const MODEL_SELF_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME
        . '.' . self::MODEL_INCLUDE_RELATIONSHIP_NAME . '.show';

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(PhysicalConditionsPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function plan_can_include_a_physical_condition()
    {
        $plan = Plan::factory()
            ->forGoal()->forFrequency()->forPhysicalCondition()
            ->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_NAME)
            ->get(route(self::MODEL_MAIN_ACTION_ROUTE, $plan));

        $response->assertSee($plan->physicalCondition->getRouteKey());

        $response->assertJsonFragment(
            [
                'related' => route(self::MODEL_RELATED_ROUTE, $plan)
            ]
        );

        $response->assertJsonFragment(
            [
                'self' => route(self::MODEL_SELF_ROUTE, $plan)
            ]
        );
    }

    /** @test */
    public function plan_can_fetch_related_physical_condition()
    {
        $plan = Plan::factory()
            ->forGoal()->forFrequency()->forPhysicalCondition()
            ->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME)
            ->get(route(self::MODEL_RELATED_ROUTE, $plan));

        $response->assertFetchedOne($plan->physicalCondition);
    }
}
