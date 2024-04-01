<?php

namespace Tests\Feature\Subcategories;

use App\Models\Subcategory;
use App\Models\User;
use Database\Seeders\PermissionsSeeders\CategoriesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IncludeCategoryTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'subcategories';
    const MODEL_INCLUDE_RELATIONSHIP_NAME = 'category';
    const MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME = 'categories';
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
            $this->seed(CategoriesPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function subcategories_can_include_categories()
    {
        $subcategory = Subcategory::factory()->forCategory()->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_NAME)
            ->get(route(self::MODEL_MAIN_ACTION_ROUTE, $subcategory));

        $response->assertSee($subcategory->category->getRouteKey());
        $response->assertJsonFragment([
            'related' => route(self::MODEL_RELATED_ROUTE, $subcategory)
        ]);
        $response->assertJsonFragment([
            'self' => route(self::MODEL_SELF_ROUTE, $subcategory)
        ]);
    }

    /** @test */
    public function subcategories_can_fetch_related_categories()
    {
        $subcategory = Subcategory::factory()->forCategory()->create();
        $category = $subcategory->category;

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME)
            ->get(route(self::MODEL_SELF_ROUTE, $subcategory));

        $response->assertFetchedToOne($category);
    }
}
