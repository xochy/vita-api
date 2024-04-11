<?php

namespace Tests\Feature\Plans;

use App\Models\Frequency;
use App\Models\Goal;
use App\Models\PhysicalCondition;
use App\Models\Plan;
use App\Models\User;
use Database\Seeders\permissionsSeeders\PlansPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UpdatePlansTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'plans';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.update';

    const MODEL_ATTRIBUTE_NAME = 'name';

    const MODEL_NAME_ATTRIBUTE_VALUE = 'name changed';

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(PlansPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function guests_users_cannot_update_plans()
    {
        $plan = Plan::factory()
            ->forGoal()->forFrequency()->forPhysicalCondition()
            ->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $plan->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_NAME_ATTRIBUTE_VALUE,
            ]
        ];

        $response = $this->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $plan->getRouteKey()));

        // Unauthorized (401)
        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_users_can_update_plans_name()
    {
        $plan = Plan::factory()
            ->forGoal()->forFrequency()->forPhysicalCondition()
            ->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $plan->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_NAME_ATTRIBUTE_VALUE,
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $plan->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas(self::MODEL_PLURAL_NAME, [
            'id' => $plan->getRouteKey(),
            self::MODEL_ATTRIBUTE_NAME => self::MODEL_NAME_ATTRIBUTE_VALUE,
        ]);
    }

    /** @test */
    public function authenticated_users_can_update_plans_goal()
    {
        $plan = Plan::factory()
            ->forGoal()->forFrequency()->forPhysicalCondition()
            ->create();

        $goal = Goal::factory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $plan->getRouteKey(),
            'relationships' => [
                'goal' => [
                    'data' => [
                        'type' => 'goals',
                        'id' => (string) $goal->getRouteKey(),
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $plan->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas(self::MODEL_PLURAL_NAME, [
            'id' => $plan->getRouteKey(),
            'goal_id' => $goal->getRouteKey(),
        ]);
    }

    /** @test */
    public function authenticated_users_can_update_plans_frequency()
    {
        $plan = Plan::factory()
            ->forGoal()->forFrequency()->forPhysicalCondition()
            ->create();

        $frequency = Frequency::factory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $plan->getRouteKey(),
            'relationships' => [
                'frequency' => [
                    'data' => [
                        'type' => 'frequencies',
                        'id' => (string) $frequency->getRouteKey(),
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $plan->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas(self::MODEL_PLURAL_NAME, [
            'id' => $plan->getRouteKey(),
            'frequency_id' => $frequency->getRouteKey(),
        ]);
    }

    /** @test */
    public function authenticated_users_can_update_plans_physical_condition()
    {
        $plan = Plan::factory()
            ->forGoal()->forFrequency()->forPhysicalCondition()
            ->create();

        $physicalCondition = PhysicalCondition::factory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $plan->getRouteKey(),
            'relationships' => [
                'physicalCondition' => [
                    'data' => [
                        'type' => 'physical-conditions',
                        'id' => (string) $physicalCondition->getRouteKey(),
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $plan->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas(self::MODEL_PLURAL_NAME, [
            'id' => $plan->getRouteKey(),
            'physical_condition_id' => $physicalCondition->getRouteKey(),
        ]);
    }

}
