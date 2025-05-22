<?php

namespace Tests\Feature\Goals;

use App\Models\Goal;
use App\Models\User;
use Database\Seeders\permissionsSeeders\GoalsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ListGoalsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'goals';
    const MODEL_SHOW_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';
    const MODEL_INDEX_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.index';

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(GoalsPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function it_can_fetch_single_goal()
    {
        $goal = Goal::factory()->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $goal));

        $response->assertFetchedOne(
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $goal->getRouteKey(),
                'attributes' => [
                    'name' => $goal->name,
                    'description' => $goal->description,
                    'slug' => $goal->slug,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $goal)
                ]
            ]
        );
    }

    /** @test */
    public function can_fetch_all_goals()
    {
        $goals = Goal::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->get(route(self::MODEL_INDEX_ACTION_ROUTE));

        $response->assertFetchedMany(
            [
                [
                    'type' => self::MODEL_PLURAL_NAME,
                    'id' => (string) $goals[0]->getRouteKey(),
                    'attributes' => [
                        'name' => $goals[0]->name,
                        'description' => $goals[0]->description,
                        'slug' => $goals[0]->slug,
                    ],
                    'links' => [
                        'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $goals[0])
                    ]
                ],
                [
                    'type' => self::MODEL_PLURAL_NAME,
                    'id' => (string) $goals[1]->getRouteKey(),
                    'attributes' => [
                        'name' => $goals[1]->name,
                        'description' => $goals[1]->description,
                        'slug' => $goals[1]->slug,
                    ],
                    'links' => [
                        'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $goals[1])
                    ]
                ],
                [
                    'type' => self::MODEL_PLURAL_NAME,
                    'id' => (string) $goals[2]->getRouteKey(),
                    'attributes' => [
                        'name' => $goals[2]->name,
                        'description' => $goals[2]->description,
                        'slug' => $goals[2]->slug,
                    ],
                    'links' => [
                        'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $goals[2])
                    ]
                ]
            ]
        );
    }

    /** @test */
    public function can_fetch_flat_goals_list()
    {
        $goals = Goal::factory()->count(3)->create();

        $params = [
            'fields[goals]' => 'name'
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->get(route(self::MODEL_INDEX_ACTION_ROUTE, $params));

        $response->assertFetchedMany(
            [
                [
                    'type' => self::MODEL_PLURAL_NAME,
                    'id' => (string) $goals[0]->getRouteKey(),
                    'attributes' => [
                        'name' => $goals[0]->name,
                    ]
                ],
                [
                    'type' => self::MODEL_PLURAL_NAME,
                    'id' => (string) $goals[1]->getRouteKey(),
                    'attributes' => [
                        'name' => $goals[1]->name,
                    ],
                ],
                [
                    'type' => self::MODEL_PLURAL_NAME,
                    'id' => (string) $goals[2]->getRouteKey(),
                    'attributes' => [
                        'name' => $goals[2]->name,
                    ]
                ]
            ]
        );
    }
}
