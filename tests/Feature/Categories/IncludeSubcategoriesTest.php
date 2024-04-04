<?php

namespace Tests\Feature\Categories;

use App\Models\Category;
use App\Models\Subcategory;
use App\Models\User;
use Database\Seeders\permissionsSeeders\SubcategoriesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IncludeSubcategoriesTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'categories';
    const MODEL_INCLUDE_RELATIONSHIP_NAME = 'subcategories';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';

    const MODEL_RELATED_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME
        . '.' . self::MODEL_INCLUDE_RELATIONSHIP_NAME;

    const MODEL_SELF_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME
        . '.' . self::MODEL_INCLUDE_RELATIONSHIP_NAME . '.show';

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
    public function category_can_include_subcategories()
    {
        $category = Category::factory()
            ->has(Subcategory::factory())
            ->create();

        $this->actingAs($this->user)->jsonApi()
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_NAME)
            ->get(route(self::MODEL_MAIN_ACTION_ROUTE, $category))
            ->assertSee($category->subcategories[0]->slug)
            ->assertJsonFragment([
                'related' => route(self::MODEL_RELATED_ROUTE, $category)
            ])
            ->assertJsonFragment([
                'self' => route(self::MODEL_SELF_ROUTE, $category)
            ]);
    }

    /** @test */
    public function category_can_fetch_related_subcategories()
    {
        $subcategories = Subcategory::factory()->count(3);
        $category = Category::factory()
            ->has($subcategories)
            ->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_NAME)
            ->get(route(self::MODEL_MAIN_ACTION_ROUTE, $category));

        $response->assertSee($category->subcategories[0]->name);
        $response->assertSee($category->subcategories[1]->name);
        $response->assertSee($category->subcategories[2]->name);

        $response->assertJsonFragment([
            'related' => route(self::MODEL_RELATED_ROUTE, $category)
        ]);

        $response->assertJsonFragment([
            'self' => route(self::MODEL_SELF_ROUTE, $category)
        ]);
    }
}
