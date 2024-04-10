<?php

namespace Tests\Feature\Routines;

use App\Models\Routine;
use App\Models\User;
use Database\Seeders\permissionsSeeders\RoutinesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaginateRoutinesTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'routines';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.index';

    const MODEL_SIZE_PARAM_NAME = 'page[size]';
    const MODEL_NUMBER_PARAM_NAME = 'page[number]';

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(RoutinesPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('user');
    }

    /** @test */
    public function can_fetch_paginated_routines()
    {
        Routine::factory()->count(10)->create();

        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                self::MODEL_SIZE_PARAM_NAME => 2, self::MODEL_NUMBER_PARAM_NAME => 3
            ]
        );

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_NUMBER_PARAM_NAME)->get($url);

        $response->assertJsonStructure([
            'links' => ['first', 'prev', 'next', 'last']
        ]);

        $response->assertJsonFragment([
            'first' => route(
                self::MODEL_MAIN_ACTION_ROUTE,
                [
                    self::MODEL_NUMBER_PARAM_NAME => 1, self::MODEL_SIZE_PARAM_NAME => 2
                ]
            )
        ]);

        $response->assertJsonFragment([
            'prev' => route(
                self::MODEL_MAIN_ACTION_ROUTE,
                [
                    self::MODEL_NUMBER_PARAM_NAME => 2, self::MODEL_SIZE_PARAM_NAME => 2
                ]
            )
        ]);

        $response->assertJsonFragment([
            'next' => route(
                self::MODEL_MAIN_ACTION_ROUTE,
                [
                    self::MODEL_NUMBER_PARAM_NAME => 4, self::MODEL_SIZE_PARAM_NAME => 2
                ]
            )
        ]);

        $response->assertJsonFragment([
            'last' => route(
                self::MODEL_MAIN_ACTION_ROUTE,
                [
                    self::MODEL_NUMBER_PARAM_NAME => 5, self::MODEL_SIZE_PARAM_NAME => 2
                ]
            )
        ]);
    }
}
