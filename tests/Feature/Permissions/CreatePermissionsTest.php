<?php

namespace Tests\Feature\Permissions;

use App\Models\User;
use Database\Seeders\permissionsSeeders\PermissionsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CreatePermissionsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'permissions';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.createPermission';
    const MODEL_ATTRIBUTE_NAME = 'name';
    const MODEL_ATTRIBUTE_DISPLAY_NAME = 'display_name';
    const MODEL_ATTRIBUTE_ACTION = 'action';
    const MODEL_ATTRIBUTE_SUBJECT = 'subject';

    const MODEL_ATTRIBUTE_NAME_VALUE = 'create articles';
    const MODEL_ATTRIBUTE_DISPLAY_NAME_VALUE = 'Crear artículos';

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
    public function unauthorized_users_cannot_create_roles()
    {
        $user = User::factory()->create()->assignRole('admin');

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME         => self::MODEL_ATTRIBUTE_NAME_VALUE,
                self::MODEL_ATTRIBUTE_DISPLAY_NAME => self::MODEL_ATTRIBUTE_DISPLAY_NAME_VALUE,
                self::MODEL_ATTRIBUTE_ACTION       => 'create',
                self::MODEL_ATTRIBUTE_SUBJECT      => 'articles',
            ]
        ];

        $response = $this->actingAs($user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Forbidden (403)
        $response->assertError(
            403,
            [
                'detail' => __('permissions.cannot_create')
            ]
        );
    }

    /** @test */
    public function authenticated_users_as_superadmin_can_create_permissions()
    {
        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME         => self::MODEL_ATTRIBUTE_NAME_VALUE,
                self::MODEL_ATTRIBUTE_DISPLAY_NAME => self::MODEL_ATTRIBUTE_DISPLAY_NAME_VALUE,
                self::MODEL_ATTRIBUTE_ACTION       => 'create',
                self::MODEL_ATTRIBUTE_SUBJECT      => 'articles',
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Created (201)
        $response->assertCreated();

        $this->assertDatabaseHas(
            'permissions',
            [
                'name'         => self::MODEL_ATTRIBUTE_NAME_VALUE,
                'display_name' => self::MODEL_ATTRIBUTE_DISPLAY_NAME_VALUE,
                'action'       => 'create',
                'subject'      => 'articles',
            ]
        );
    }
}
