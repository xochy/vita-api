<?php

namespace Tests\Feature\Permissions;

use App\Models\User;
use Database\Seeders\permissionsSeeders\PermissionsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UpdatePermissionsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'permissions';
    const MODEL_ATTRIBUTE_NAME = 'name';
    const MODEL_ATTRIBUTE_DISPLAY_NAME = 'display_name';
    const MODEL_ATTRIBUTE_ACTION = 'action';
    const MODEL_ATTRIBUTE_SUBJECT = 'subject';
    const MODEL_NAME_ATTRIBUTE_VALUE = 'name changed';
    const MODEL_DISPLAY_NAME_ATTRIBUTE_VALUE = 'display name changed';
    const MODEL_ACTION_ATTRIBUTE_VALUE = 'update';
    const MODEL_SUBJECT_ATTRIBUTE_VALUE = 'permissions';

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
    public function unauthorized_users_cannot_update_permissions()
    {
        $user = User::factory()->create()->assignRole('admin');

        $permission = Permission::findByName('delete permissions');

        $this->assertEquals('delete permissions', $permission->name);

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $permission->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME         => self::MODEL_NAME_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_DISPLAY_NAME => self::MODEL_DISPLAY_NAME_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_ACTION       => self::MODEL_ACTION_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_SUBJECT      => self::MODEL_SUBJECT_ATTRIBUTE_VALUE,
            ]
        ];

        $response = $this->actingAs($user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route('v1.permissions.updatePermission', $permission->getRouteKey()));

        // Forbidden (403)
        $response->assertError(
            403,
            [
                'detail' => __('permissions.cannot_update')
            ]
        );
    }

    /** @test */
    public function authenticated_users_as_superadmin_can_update_roles()
    {
        $permission = Permission::findByName('read permissions');

        $this->assertEquals('read permissions', $permission->name);

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $permission->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME         => self::MODEL_NAME_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_DISPLAY_NAME => self::MODEL_DISPLAY_NAME_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_ACTION       => self::MODEL_ACTION_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_SUBJECT      => self::MODEL_SUBJECT_ATTRIBUTE_VALUE,
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route('v1.permissions.updatePermission', $permission->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);
    }
}
