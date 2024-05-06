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

class CreatePlansTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'plans';
    const MODEL_INCLUDE_GOAL_RELATIONSHIP_SINGLE_NAME = 'goal';
    const MODEL_INCLUDE_GOAL_RELATIONSHIP_PLURAL_NAME = 'goals';
    const MODEL_INCLUDE_FREQUENCY_RELATIONSHIP_SINGLE_NAME = 'frequency';
    const MODEL_INCLUDE_FREQUENCY_RELATIONSHIP_PLURAL_NAME = 'frequencies';
    const MODEL_INCLUDE_PHYSICAL_CONDITION_RELATIONSHIP_SINGLE_NAME = 'physicalCondition';
    const MODEL_INCLUDE_PHYSICAL_CONDITION_RELATIONSHIP_PLURAL_NAME = 'physical-conditions';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.store';

    const MODEL_ATTRIBUTE_NAME = 'name';

    protected User $user;
    protected Goal $goal;
    protected Frequency $frequency;
    protected PhysicalCondition $physicalCondition;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(PlansPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('user');

        // Models for relationships
        $this->goal = Goal::factory()->create();
        $this->frequency = Frequency::factory()->create();
        $this->physicalCondition = PhysicalCondition::factory()->create();
    }

    /** @test */
    public function guests_users_cannot_create_plans()
    {
        $plan = array_filter(Plan::factory()->raw());

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $plan
        ];

        $response = $this->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unauthorized (401)
        $response->assertStatus(401);

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $plan);
    }

    /** @test */
    public function authenticated_users_can_create_plans()
    {
        $plan = array_filter(Plan::factory()->raw());

        $data = [
            'type'       => self::MODEL_PLURAL_NAME,
            'attributes' => $plan,
            'relationships' => [
                self::MODEL_INCLUDE_GOAL_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::MODEL_INCLUDE_GOAL_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->goal->getRouteKey()
                    ]
                ],
                self::MODEL_INCLUDE_FREQUENCY_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::MODEL_INCLUDE_FREQUENCY_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->frequency->getRouteKey()
                    ]
                ],
                self::MODEL_INCLUDE_PHYSICAL_CONDITION_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::MODEL_INCLUDE_PHYSICAL_CONDITION_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->physicalCondition->getRouteKey()
                    ]
                ]
            ]
        ];

        $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::MODEL_INCLUDE_GOAL_RELATIONSHIP_SINGLE_NAME)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE))
            ->assertCreated();

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                self::MODEL_ATTRIBUTE_NAME => $plan[self::MODEL_ATTRIBUTE_NAME],

                // Relationships
                'goal_id' => $this->goal->getRouteKey(),
                'frequency_id' => $this->frequency->getRouteKey(),
                'physical_condition_id' => $this->physicalCondition->getRouteKey(),
            ]
        );
    }

    /** @test */
    public function plan_name_is_required()
    {
        $plan = Plan::factory()->raw(
            [
                'name' => ''
            ]
        );

        $data = [
            'type'       => self::MODEL_PLURAL_NAME,
            'attributes' => $plan,
            'relationships' => [
                self::MODEL_INCLUDE_GOAL_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::MODEL_INCLUDE_GOAL_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->goal->getRouteKey()
                    ]
                ],
                self::MODEL_INCLUDE_FREQUENCY_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::MODEL_INCLUDE_FREQUENCY_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->frequency->getRouteKey()
                    ]
                ],
                self::MODEL_INCLUDE_PHYSICAL_CONDITION_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::MODEL_INCLUDE_PHYSICAL_CONDITION_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->physicalCondition->getRouteKey()
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => '/data/attributes/name'],
                'detail' => 'The name field is required.'
            ]
        );

        $response->assertSee('data\/attributes\/name');

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $plan);
    }

    /** @test */
    public function plan_goal_is_required()
    {
        $plan = Plan::factory()->raw();

        $data = [
            'type'       => self::MODEL_PLURAL_NAME,
            'attributes' => $plan,
            'relationships' => [
                self::MODEL_INCLUDE_FREQUENCY_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::MODEL_INCLUDE_FREQUENCY_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->frequency->getRouteKey()
                    ]
                ],
                self::MODEL_INCLUDE_PHYSICAL_CONDITION_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::MODEL_INCLUDE_PHYSICAL_CONDITION_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->physicalCondition->getRouteKey()
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => '/data/relationships/goal'],
                'detail' => 'The goal field is required.'
            ]
        );

        $response->assertSee('data\/relationships\/goal');

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $plan);
    }

    /** @test */
    public function plan_frequency_is_required()
    {
        $plan = Plan::factory()->raw();

        $data = [
            'type'       => self::MODEL_PLURAL_NAME,
            'attributes' => $plan,
            'relationships' => [
                self::MODEL_INCLUDE_GOAL_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::MODEL_INCLUDE_GOAL_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->goal->getRouteKey()
                    ]
                ],
                self::MODEL_INCLUDE_PHYSICAL_CONDITION_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::MODEL_INCLUDE_PHYSICAL_CONDITION_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->physicalCondition->getRouteKey()
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => '/data/relationships/frequency'],
                'detail' => 'The frequency field is required.'
            ]
        );

        $response->assertSee('data\/relationships\/frequency');

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $plan);
    }

    /** @test */
    public function plan_physical_condition_is_required()
    {
        $plan = Plan::factory()->raw();

        $data = [
            'type'       => self::MODEL_PLURAL_NAME,
            'attributes' => $plan,
            'relationships' => [
                self::MODEL_INCLUDE_GOAL_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::MODEL_INCLUDE_GOAL_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->goal->getRouteKey()
                    ]
                ],
                self::MODEL_INCLUDE_FREQUENCY_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::MODEL_INCLUDE_FREQUENCY_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->frequency->getRouteKey()
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => '/data/relationships/physicalCondition'],
                'detail' => 'The physical condition field is required.'
            ]
        );

        $response->assertSee('data\/relationships\/physicalCondition');

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $plan);
    }
}
