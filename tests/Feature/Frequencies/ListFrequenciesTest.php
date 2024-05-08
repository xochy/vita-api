<?php

namespace Tests\Feature\Frequencies;

use App\Models\Frequency;
use App\Models\User;
use Database\Seeders\permissionsSeeders\FrequenciesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ListFrequenciesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    const MODEL_PLURAL_NAME = 'frequencies';
    const MODEL_SHOW_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';
    const MODEL_INDEX_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.index';

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(FrequenciesPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function it_can_fetch_single_frequency()
    {
        $frequency = Frequency::factory()->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $frequency));

        $response->assertFetchedOne(
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $frequency->getRouteKey(),
                'attributes' => [
                    'name'        => $frequency->name,
                    'description' => $frequency->description,
                    'slug'        => $frequency->slug,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $frequency)
                ]
            ]
        );
    }

    /** @test */
    public function can_fetch_all_frequencies()
    {
        $frequencies = Frequency::factory(3)->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->get(route(self::MODEL_INDEX_ACTION_ROUTE));

        $response->assertFetchedMany(
            [
                [
                    'type' => self::MODEL_PLURAL_NAME,
                    'id' => (string) $frequencies[0]->getRouteKey(),
                    'attributes' => [
                        'name'        => $frequencies[0]->name,
                        'description' => $frequencies[0]->description,
                        'slug'        => $frequencies[0]->slug,
                    ],
                    'links' => [
                        'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $frequencies[0])
                    ]
                ],
                [
                    'type' => self::MODEL_PLURAL_NAME,
                    'id' => (string) $frequencies[1]->getRouteKey(),
                    'attributes' => [
                        'name'        => $frequencies[1]->name,
                        'description' => $frequencies[1]->description,
                        'slug'        => $frequencies[1]->slug,
                    ],
                    'links' => [
                        'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $frequencies[1])
                    ]
                ],
                [
                    'type' => self::MODEL_PLURAL_NAME,
                    'id' => (string) $frequencies[2]->getRouteKey(),
                    'attributes' => [
                        'name'        => $frequencies[2]->name,
                        'description' => $frequencies[2]->description,
                        'slug'        => $frequencies[2]->slug,
                    ],
                    'links' => [
                        'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $frequencies[2])
                    ]
                ]
            ]
        );
    }
}
