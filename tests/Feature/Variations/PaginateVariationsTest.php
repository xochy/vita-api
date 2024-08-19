<?php

namespace Tests\Feature\Variations;

use App\Models\User;
use App\Models\Variation;
use App\Models\Workout;
use Database\Seeders\permissionsSeeders\VariationsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaginateVariationsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'variations';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.index';

    const MODEL_SIZE_PARAM_NAME = 'page[size]';
    const MODEL_NUMBER_PARAM_NAME = 'page[number]';

    protected User $user;
    protected Workout $workout;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(VariationsPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
        $this->workout = Workout::factory()->forCategory()->create();
    }

    /** @test */
    public function can_fetch_paginated_variations()
    {
        Variation::factory()->for($this->workout)->count(10)->create();

        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                self::MODEL_SIZE_PARAM_NAME => 2,
                self::MODEL_NUMBER_PARAM_NAME => 3
            ]
        );

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_NUMBER_PARAM_NAME)->get($url);

        $response->assertJsonStructure(
            [
                'links' => ['first', 'prev', 'next', 'last']
            ]

        );

        $response->assertJsonFragment(
            [
                'first' => route(
                    self::MODEL_MAIN_ACTION_ROUTE,
                    [
                        self::MODEL_NUMBER_PARAM_NAME => 1,
                        self::MODEL_SIZE_PARAM_NAME => 2
                    ]
                ),
                'prev' => route(
                    self::MODEL_MAIN_ACTION_ROUTE,
                    [
                        self::MODEL_NUMBER_PARAM_NAME => 2,
                        self::MODEL_SIZE_PARAM_NAME => 2
                    ]
                ),
                'next' => route(
                    self::MODEL_MAIN_ACTION_ROUTE,
                    [
                        self::MODEL_NUMBER_PARAM_NAME => 4,
                        self::MODEL_SIZE_PARAM_NAME => 2
                    ]
                ),
                'last' => route(
                    self::MODEL_MAIN_ACTION_ROUTE,
                    [
                        self::MODEL_NUMBER_PARAM_NAME => 5,
                        self::MODEL_SIZE_PARAM_NAME => 2
                    ]
                ),
            ]
        );
    }
}
