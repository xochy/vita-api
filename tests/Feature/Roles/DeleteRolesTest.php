<?php

namespace Tests\Feature\Roles;

use App\Models\User;
use Database\Seeders\permissionsSeeders\RolesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DeleteRolesTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'roles';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.deleteRole';

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(RolesPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('superAdmin');
    }

    /** @test */
    public function unauthorized_users_cannot_delete_roles()
    {
        $user = User::factory()->create()->assignRole('admin');

        $role = Role::create(
            [
                'name'         => 'Developer',
                'display_name' => 'Desarrollador',
                'default'      => false
            ]
        );

        $response =  $this->actingAs($user)->jsonApi()
            ->delete(route(self::MODEL_MAIN_ACTION_ROUTE, $role->getRouteKey()));

        // Forbidden (403)
        $response->assertError(
            403,
            [
                'detail' => __('roles.cannot_delete')
            ]
        );
    }

    /** @test */
    public function authenticated_users_as_superadmin_can_delete_roles()
    {
        $role = Role::create(
            [
                'name'         => 'Developer',
                'display_name' => 'Desarrollador',
                'default'      => false
            ]
        );

        $response = $this->actingAs($this->user)->jsonApi()
            ->delete(route(self::MODEL_MAIN_ACTION_ROUTE, $role->getRouteKey()));

        // No Content (204)
        $response->assertStatus(204);
        $response->assertNoContent();

        $this->assertDatabaseMissing(
            self::MODEL_PLURAL_NAME,
            [
                'id' => $role->id
            ]
        );
    }
}
