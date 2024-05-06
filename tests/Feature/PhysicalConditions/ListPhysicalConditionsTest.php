<?php

namespace Tests\Feature\PhysicalConditions;

use App\Models\PhysicalCondition;
use App\Models\User;
use Database\Seeders\permissionsSeeders\PhysicalConditionsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ListPhysicalConditionsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'physical-conditions';
    const MODEL_SHOW_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';
    const MODEL_INDEX_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.index';

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
    public function it_can_fetch_single_physical_condition()
    {
        $physicalCondition = PhysicalCondition::factory()->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $physicalCondition));

        $response->assertFetchedOne($physicalCondition);

        $response->assertFetchedOne(
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $physicalCondition->getRouteKey(),
                'attributes' => [
                    'name' => $physicalCondition->name,
                    'description' => $physicalCondition->description,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $physicalCondition)
                ]
            ]
        );
    }

    /** @test */
    public function can_fetch_all_physical_conditions()
    {
        $physicalConditions = PhysicalCondition::factory()->times(3)->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->get(route(self::MODEL_INDEX_ACTION_ROUTE));

        $response->assertFetchedMany($physicalConditions);

        $response->assertFetchedMany(
            [
                [
                    'type' => self::MODEL_PLURAL_NAME,
                    'id' => $physicalConditions[0]->getRouteKey(),
                    'attributes' => [
                        'name'        => $physicalConditions[0]->name,
                        'description' => $physicalConditions[0]->description,
                    ],
                    'links' => [
                        'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $physicalConditions[0])
                    ]
                ],
                [
                    'type' => self::MODEL_PLURAL_NAME,
                    'id' => $physicalConditions[1]->getRouteKey(),
                    'attributes' => [
                        'name'        => $physicalConditions[1]->name,
                        'description' => $physicalConditions[1]->description,
                    ],
                    'links' => [
                        'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $physicalConditions[1])
                    ]
                ],
                [
                    'type' => self::MODEL_PLURAL_NAME,
                    'id' => $physicalConditions[2]->getRouteKey(),
                    'attributes' => [
                        'name'        => $physicalConditions[2]->name,
                        'description' => $physicalConditions[2]->description,
                    ],
                    'links' => [
                        'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $physicalConditions[2])
                    ]
                ],
            ]
        );
    }
}
