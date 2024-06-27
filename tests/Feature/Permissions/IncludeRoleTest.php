<?php

namespace Tests\Feature\Permissions;

use App\Models\User;
use Database\Seeders\permissionsSeeders\PermissionsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IncludeRoleTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'permissions';
    const MODEL_INCLUDE_RELATIONSHIP_NAME = 'roles';
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
            $this->seed(PermissionsPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('superAdmin');
    }

    /** @test */
    public function permission_can_include_roles()
    {
        $permission = Permission::findByName('read permissions');

        $response = $this->actingAs($this->user)->jsonApi()
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_NAME)
            ->get(route(self::MODEL_MAIN_ACTION_ROUTE, $permission));

        $response->assertJsonFragment(
            [
                'related' => route(self::MODEL_RELATED_ROUTE, $permission)
            ]
        );

        $response->assertJsonFragment(
            [
                'self' => route(self::MODEL_SELF_ROUTE, $permission)
            ]
        );
    }

    /** @test */
    public function permissions_can_fetch_related_roles()
    {
        $permission = Permission::findByName('read permissions');

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_NAME)
            ->get(route(self::MODEL_MAIN_ACTION_ROUTE, $permission));

        $roles = $permission->roles()->get()->toArray();

        foreach ($roles as $role) {
            $response->assertSee($role['name']);
        }
    }
}
