<?php

namespace Tests\Feature\Subcategories;

use App\Models\Subcategory;
use App\Models\User;
use Database\Seeders\permissionsSeeders\SubcategoriesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DeleteSubcategoriesTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'subcategories';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.destroy';

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
    public function guests_users_cannot_delete_subcategories()
    {
        $subcategory = Subcategory::factory()->forCategory()->create();

        $response = $this->jsonApi()
            ->delete(route(self::MODEL_MAIN_ACTION_ROUTE, $subcategory->getRouteKey()));

        // Unauthorized (401)
        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_users_as_admin_can_delete_categories()
    {
        $subcategory = Subcategory::factory()->forCategory()->create();

        $response = $this->actingAs($this->user)
            ->jsonApi()
            ->delete(route(self::MODEL_MAIN_ACTION_ROUTE, $subcategory->getRouteKey()));

        // No Content (204)
        $response->assertStatus(204);
        $response->assertNoContent();

        $this->assertDatabaseMissing(
            self::MODEL_PLURAL_NAME,
            [
                'id' => $subcategory->getKey()
            ]
        );
    }
}
