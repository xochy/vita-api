<?php

namespace Tests\Feature\Plans;

use App\Models\Plan;
use App\Models\User;
use Database\Seeders\permissionsSeeders\PlansPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ListPlansTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'plans';
    const MODEL_SHOW_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';
    const MODEL_INDEX_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.index';

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
    public function it_can_fetch_single_plan()
    {
        $plan = Plan::factory()->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $plan));

        $response->assertFetchedOne($plan);

        $response->assertFetchedOne([
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $plan->getRouteKey(),
            'attributes' => [
                'name' => $plan->name,
            ],
            'links' => [
                'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $plan)
            ]
        ]);
    }

        /** @test */
        public function can_fetch_all_plans()
        {
            $categories = Plan::factory()->times(3)->create();

            $response = $this->actingAs($this->user)->jsonApi()
                ->expects(self::MODEL_PLURAL_NAME)
                ->get(route(self::MODEL_INDEX_ACTION_ROUTE));

            $response->assertFetchedMany($categories);

            $response->assertFetchedMany([
                [
                    'type' => self::MODEL_PLURAL_NAME,
                    'id' => $categories[0]->getRouteKey(),
                    'attributes' => [
                        'name'        => $categories[0]->name,
                    ],
                    'links' => [
                        'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $categories[0])
                    ]
                ],
                [
                    'type' => self::MODEL_PLURAL_NAME,
                    'id' => $categories[1]->getRouteKey(),
                    'attributes' => [
                        'name'        => $categories[1]->name,
                    ],
                    'links' => [
                        'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $categories[1])
                    ]
                ],
                [
                    'type' => self::MODEL_PLURAL_NAME,
                    'id' => $categories[2]->getRouteKey(),
                    'attributes' => [
                        'name'        => $categories[2]->name,
                    ],
                    'links' => [
                        'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $categories[2])
                    ]
                ],
            ]);
        }
}
