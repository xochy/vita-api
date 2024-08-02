<?php

namespace Tests\Feature\Workouts;

use App\Models\Category;
use App\Models\User;
use App\Models\Workout;
use Database\Seeders\permissionsSeeders\WorkoutsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaginateWorkoutsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'workouts';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.index';

    const MODEL_SIZE_PARAM_NAME = 'page[size]';
    const MODEL_NUMBER_PARAM_NAME = 'page[number]';

    protected User $user;
    protected Category $category;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(WorkoutsPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
        $this->category = Category::factory()->create();
    }

    /** @test */
    public function can_fetch_paginated_workouts()
    {
        Workout::factory()->for($this->category)->count(10)->create();

        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                self::MODEL_SIZE_PARAM_NAME => 2, self::MODEL_NUMBER_PARAM_NAME => 3
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
                        self::MODEL_NUMBER_PARAM_NAME => 1, self::MODEL_SIZE_PARAM_NAME => 2
                    ]
                ),
                'prev'  => route(
                    self::MODEL_MAIN_ACTION_ROUTE,
                    [
                        self::MODEL_NUMBER_PARAM_NAME => 2, self::MODEL_SIZE_PARAM_NAME => 2
                    ]
                ),
                'next'  => route(
                    self::MODEL_MAIN_ACTION_ROUTE,
                    [
                        self::MODEL_NUMBER_PARAM_NAME => 4, self::MODEL_SIZE_PARAM_NAME => 2
                    ]
                ),
                'last'  => route(
                    self::MODEL_MAIN_ACTION_ROUTE,
                    [
                        self::MODEL_NUMBER_PARAM_NAME => 5, self::MODEL_SIZE_PARAM_NAME => 2
                    ]
                ),
            ]
        );
    }
}
