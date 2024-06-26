<?php

namespace Tests\Feature\Roles;

use App\Models\User;
use Database\Seeders\permissionsSeeders\RolesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IncludePermissionsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'roles';
    const MODEL_INCLUDE_RELATIONSHIP_NAME = 'permissions';
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
            $this->seed(RolesPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('superAdmin');
    }

    /** @test */
    public function role_can_include_permissions()
    {
        $role = Role::findByName('superAdmin');

        $response = $this->actingAs($this->user)->jsonApi()
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_NAME)
            ->get(route(self::MODEL_MAIN_ACTION_ROUTE, $role))->dump();
    }
}
