<?php

namespace Tests\Feature\Subcategories;

use App\Models\Subcategory;
use App\Models\User;
use Database\Seeders\permissionsSeeders\SubcategoriesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ListSubcategoriesTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'subcategories';
    const MODEL_SHOW_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';
    const MODEL_INDEX_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.index';

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(SubcategoriesPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function it_can_fetch_single_subcategory()
    {
        $subcategory = Subcategory::factory()->forCategory()->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $subcategory));

        $response->assertFetchedOne(
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $subcategory->getRouteKey(),
                'attributes' => [
                    'name'        => $subcategory->name,
                    'description' => $subcategory->description,
                    'slug'        => $subcategory->slug,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $subcategory)
                ]
            ]
        );
    }

    /** @test */
    public function can_fetch_all_subcategories()
    {
        $subcategories = Subcategory::factory()->forCategory()->count(3)->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->get(route(self::MODEL_INDEX_ACTION_ROUTE));

        $response->assertFetchedMany(
            [
                [
                    'type' => self::MODEL_PLURAL_NAME,
                    'id' => (string) $subcategories[0]->getRouteKey(),
                    'attributes' => [
                        'name'        => $subcategories[0]->name,
                        'description' => $subcategories[0]->description,
                        'slug'        => $subcategories[0]->slug,
                    ],
                    'links' => [
                        'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $subcategories[0])
                    ]
                ],
                [
                    'type' => self::MODEL_PLURAL_NAME,
                    'id' => (string) $subcategories[1]->getRouteKey(),
                    'attributes' => [
                        'name'        => $subcategories[1]->name,
                        'description' => $subcategories[1]->description,
                        'slug'        => $subcategories[1]->slug,
                    ],
                    'links' => [
                        'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $subcategories[1])
                    ]
                ],
                [
                    'type' => self::MODEL_PLURAL_NAME,
                    'id' => (string) $subcategories[2]->getRouteKey(),
                    'attributes' => [
                        'name'        => $subcategories[2]->name,
                        'description' => $subcategories[2]->description,
                        'slug'        => $subcategories[2]->slug,
                    ],
                    'links' => [
                        'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $subcategories[2])
                    ]
                ]
            ]
        );
    }
}
