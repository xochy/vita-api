<?php

namespace Tests\Feature\Roles;

use App\Models\User;
use Database\Seeders\permissionsSeeders\RolesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CreateRolesTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'roles';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.createRole';
    const MODEL_ATTRIBUTE_NAME = 'name';
    const MODEL_ATTRIBUTE_DISPLAY_NAME = 'display_name';

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
    public function unauthorized_users_cannot_create_roles()
    {
        $user = User::factory()->create()->assignRole('admin');

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME => 'Developer',
                self::MODEL_ATTRIBUTE_DISPLAY_NAME => 'Desarrollador',
            ]
        ];

        $response = $this->actingAs($user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Forbidden (403)
        $response->assertError(
            403,
            [
                'detail' => __('roles.cannot_create')
            ]
        );
    }

    /** @test */
    public function authenticated_users_as_superadmin_can_create_roles()
    {
        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME => 'Developer',
                self::MODEL_ATTRIBUTE_DISPLAY_NAME => 'Desarrollador',
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Created (201)
        $response->assertCreated();

        $this->assertDatabaseHas(
            'roles',
            [
                'name'         => 'Developer',
                'display_name' => 'Desarrollador',
                'default'      => false
            ]
        );
    }

    /** @test */
    public function roles_name_is_required()
    {
        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME => '',
                self::MODEL_ATTRIBUTE_DISPLAY_NAME => 'Desarrollador',
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Bad request (400)
        $response->assertError(
            400,
            [
                'detail' => __('validation.required', [
                    'attribute' => __('validation.attributes.name')
                ])
            ]
        );
    }

    /** @test */
    public function roles_display_name_is_required()
    {
        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME => 'Developer',
                self::MODEL_ATTRIBUTE_DISPLAY_NAME => '',
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Bad request (400)
        $response->assertError(
            400,
            [
                'detail' => __('validation.required', [
                    'attribute' => __('validation.attributes.display_name')
                ])
            ]
        );
    }

    /** @test */
    public function roles_name_must_be_a_string()
    {
        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME => 123,
                self::MODEL_ATTRIBUTE_DISPLAY_NAME => 'Desarrollador',
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Bad request (400)
        $response->assertError(
            400,
            [
                'detail' => __('validation.string', [
                    'attribute' => __('validation.attributes.name')
                ])
            ]
        );
    }

    /** @test */
    public function roles_display_name_must_be_a_string()
    {
        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME => 'Developer',
                self::MODEL_ATTRIBUTE_DISPLAY_NAME => 123,
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Bad request (400)
        $response->assertError(
            400,
            [
                'detail' => __('validation.string', [
                    'attribute' => __('validation.attributes.display_name')
                ])
            ]
        );
    }

    /** @test */
    public function roles_name_must_be_unique()
    {
        Role::create(
            [
                'name' => 'Developer',
                'display_name' => 'Desarrollador',
                'default' => true
            ]
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME => 'Developer',
                self::MODEL_ATTRIBUTE_DISPLAY_NAME => 'Desarrollador',
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Bad request (400)
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
