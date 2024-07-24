<?php

namespace Tests\Feature\Roles;

use App\Models\User;
use Database\Seeders\permissionsSeeders\RolesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UpdateRolesTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'roles';
    const MODEL_ATTRIBUTE_NAME = 'name';
    const MODEL_ATTRIBUTE_DISPLAY_NAME = 'display_name';
    const MODEL_NAME_ATTRIBUTE_VALUE = 'name changed';
    const MODEL_DISPLAY_NAME_ATTRIBUTE_VALUE = 'display name changed';

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
    public function unauthorized_users_cannot_update_roles()
    {
        $user = User::factory()->create()->assignRole('admin');

        Role::create(
            [
                'name'         => 'Developer',
                'display_name' => 'Desarrollador',
                'default'      => true
            ]
        );

        $role = Role::findByName('Developer');

        $this->assertEquals('Developer', $role->name);

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $role->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_NAME_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_DISPLAY_NAME => self::MODEL_DISPLAY_NAME_ATTRIBUTE_VALUE,
            ]
        ];

        $response = $this->actingAs($user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route('v1.roles.updateRole', $role->getRouteKey()));

        // Forbidden (403)
        $response->assertError(
            403,
            [
                'detail' => __('roles.cannot_update')
            ]
        );
    }

    /** @test */
    public function authenticated_users_as_superadmin_can_update_roles()
    {
        Role::create(
            [
                'name'         => 'Developer',
                'display_name' => 'Desarrollador',
                'default'      => true
            ]
        );

        $role = Role::findByName('Developer');

        $this->assertEquals('Developer', $role->name);

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $role->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_NAME_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_DISPLAY_NAME => self::MODEL_DISPLAY_NAME_ATTRIBUTE_VALUE,
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route('v1.roles.updateRole', $role->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $role = Role::find($role->getRouteKey());

        $this->assertEquals(self::MODEL_NAME_ATTRIBUTE_VALUE, $role->name);
        $this->assertEquals(self::MODEL_DISPLAY_NAME_ATTRIBUTE_VALUE, $role->display_name);
    }

    /** @test */
    public function cannot_update_the_roles_name_if_exists()
    {
        Role::create(
            [
                'name'         => 'Developer',
                'display_name' => 'Desarrollador',
                'default'      => true
            ]
        );

        $role = Role::findByName('Developer');

        $this->assertEquals('Developer', $role->name);

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $role->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME => 'user',
                self::MODEL_ATTRIBUTE_DISPLAY_NAME => self::MODEL_DISPLAY_NAME_ATTRIBUTE_VALUE,
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route('v1.roles.updateRole', $role->getRouteKey()));

        // Wrong request (400)
        $response->assertError(
            400,
            [
                'detail' => __('validation.unique', [
                    'attribute' => __('validation.attributes.name')
                ])
            ]
        );
    }
}
