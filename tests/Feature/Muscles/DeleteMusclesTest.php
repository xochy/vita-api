<?php

namespace Tests\Feature\Muscles;

use App\Models\Muscle;
use App\Models\User;
use Database\Seeders\permissionsSeeders\MusclesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DeleteMusclesTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'muscles';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.destroy';

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(MusclesPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function guests_users_cannot_delete_categories()
    {
        $muscle = Muscle::factory()->create();

        $response = $this->jsonApi()
            ->delete(route(self::MODEL_MAIN_ACTION_ROUTE, $muscle->getRouteKey()));

        // Unauthorized (401)
        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_users_as_admin_can_delete_categories()
    {
        $muscle = Muscle::factory()->create();

        $response = $this->actingAs($this->user)
            ->jsonApi()
            ->delete(route(self::MODEL_MAIN_ACTION_ROUTE, $muscle->getRouteKey()));

        // No Content (204)
        $response->assertStatus(204);
        $response->assertNoContent();

        $this->assertDatabaseMissing(
            self::MODEL_PLURAL_NAME,
            [
                'id' => $muscle->getKey()
            ]
        );
    }
}
