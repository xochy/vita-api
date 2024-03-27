<?php

namespace Tests\Feature\Categories;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use Laravel\Sanctum\Sanctum;
use Database\Seeders\RoleSeeder;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\categories\CategoriesPermissionsSeeder;

class PaginateCategoriesTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'categories';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.index';

    const MODEL_SIZE_PARAM_NAME = 'page[size]';
    const MODEL_NUMBER_PARAM_NAME = 'page[number]';

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(CategoriesPermissionsSeeder::class);
        }

        Sanctum::actingAs(User::factory()->create()->assignRole('admin'));
    }

    /** @test */
    public function can_fetch_paginated_categories()
    {
        Category::factory()->times(10)->create();

        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                self::MODEL_SIZE_PARAM_NAME => 2, self::MODEL_NUMBER_PARAM_NAME => 3
            ]
        );

        $response = $this->jsonApi()->expects(self::MODEL_NUMBER_PARAM_NAME)->get($url);

        $response->assertJsonStructure([
            'links' => ['first', 'prev', 'next', 'last']
        ]);

        $response->assertJsonFragment([
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
        ]);
    }
}
