<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Database\Seeders\permissionsSeeders\UsersPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DeleteUsersTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'users';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.'. self::MODEL_PLURAL_NAME .'.destroy';

    protected User $user;
    protected User $superAdmin;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(UsersPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('user');
        $this->superAdmin = User::factory()->create()->assignRole('superAdmin');
    }

    /** @test */
    public function guests_users_cannot_delete_users()
    {
        $user = User::factory()->create();

        $response = $this->jsonApi()
            ->delete(route(self::MODEL_MAIN_ACTION_ROUTE, $user));

        // Unauthorized (401)
        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_users_cannot_delete_others_users()
    {
        $user = User::factory()->create()->assignRole('user');

        $response = $this->actingAs($this->user)
            ->jsonApi()
            ->delete(route(self::MODEL_MAIN_ACTION_ROUTE, $user));

        // Forbidden (403)
        $response->assertStatus(403);
    }

    /** @test */
    public function authenticated_users_can_delete_itself()
    {
        $response = $this->actingAs($this->user)->jsonApi()
            ->delete(route(self::MODEL_MAIN_ACTION_ROUTE, $this->user));

        // No Content (204)
        $response->assertStatus(204);
        $response->assertNoContent();

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, [
            'id' => $this->user->getKey()
        ]);
    }
}
