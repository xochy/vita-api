<?php

namespace Tests\Feature\Permissions;

use App\Models\User;
use Database\Seeders\permissionsSeeders\PermissionsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DeletePermissionsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'permissions';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.deletePermission';

    protected User $user;
    protected string $token;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(PermissionsPermissionsSeeder::class);
        }

        [$this->user, $this->token] = $this->createUserWithToken('superAdmin');
    }

    /** @test */
    public function unauthorized_users_cannot_delete_permissions()
    {
        [$user, $token] = $this->createUserWithToken();

        $permission = Permission::findByName('read permissions');

        $response = $this->actingAs($user)->jsonApi()
            ->withHeader('Authorization', $token)
            ->delete(
                route(
                    self::MODEL_MAIN_ACTION_ROUTE,
                    $permission->getRouteKey()
                )
            );

        // Forbidden (403)
        $response->assertError(
            403,
            [
                'detail' => __('permissions.cannot_delete')
            ]
        );
    }

    /** @test */
    public function authenticated_users_as_superadmin_can_delete_permissions()
    {
        $permission = Permission::findByName('read permissions');

        $response = $this->actingAs($this->user)->jsonApi()
            ->withHeader('Authorization', $this->token)
            ->delete(
                route(
                    self::MODEL_MAIN_ACTION_ROUTE,
                    $permission->getRouteKey()
                )
            );

        // No Content (204)
        $response->assertStatus(204);
        $response->assertNoContent();

        $this->assertDatabaseMissing(
            self::MODEL_PLURAL_NAME,
            [
                'id' => $permission->id
            ]
        );
    }
}
