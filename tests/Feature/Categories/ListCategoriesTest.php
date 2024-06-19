<?php

namespace Tests\Feature\Categories;

use App\Models\Category;
use App\Models\User;
use Database\Seeders\PermissionsSeeders\CategoriesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ListCategoriesTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'categories';
    const MODEL_SHOW_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';
    const MODEL_INDEX_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.index';

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(CategoriesPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function it_can_fetch_single_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $category));

        $response->assertFetchedOne(
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $category->getRouteKey(),
                'attributes' => [
                    'name'        => $category->name,
                    'description' => $category->description,
                    'slug'        => $category->slug,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $category)
                ]
            ]
        );
    }

    /** @test */
    public function can_fetch_all_categories()
    {
        $categories = Category::factory()->times(3)->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->get(route(self::MODEL_INDEX_ACTION_ROUTE));

        $response->assertFetchedMany(
            [
                [
                    'type' => self::MODEL_PLURAL_NAME,
                    'id' => $categories[0]->getRouteKey(),
                    'attributes' => [
                        'name'        => $categories[0]->name,
                        'description' => $categories[0]->description,
                        'slug'        => $categories[0]->slug,
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
                        'description' => $categories[1]->description,
                        'slug'        => $categories[1]->slug,
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
                        'description' => $categories[2]->description,
                        'slug'        => $categories[2]->slug,
                    ],
                    'links' => [
                        'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $categories[2])
                    ]
                ],
            ]
        );
    }
}
