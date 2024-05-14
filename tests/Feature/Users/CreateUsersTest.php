<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\permissionsSeeders\UsersPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CreateUsersTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'users';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.store';

    const MODEL_ATTRIBUTE_NAME = 'name';
    const MODEL_ATTRIBUTE_EMAIL = 'email';
    const MODEL_ATTRIBUTE_PASSWORD = 'password';
    const MODEL_ATTRIBUTE_PASSWORD_CONFIRMATION = 'password_confirmation';
    const MODEL_ATTRIBUTE_NAME_VALUE = 'John Doe';
    const MODEL_ATTRIBUTE_EMAIL_VALUE = 'test.email@mail.com';

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(UsersPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function guests_users_cannot_create_users()
    {
        $user = array_filter(
            [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_ATTRIBUTE_NAME_VALUE,
                self::MODEL_ATTRIBUTE_EMAIL => self::MODEL_ATTRIBUTE_EMAIL_VALUE,
            ]
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $user
        ];

        $response = $this->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unauthorized (401)
        $response->assertStatus(401);

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $user);
    }

    /** @test */
    public function authenticated_users_as_admin_can_create_users()
    {
        $user = array_filter(
            [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_ATTRIBUTE_NAME_VALUE,
                self::MODEL_ATTRIBUTE_EMAIL => self::MODEL_ATTRIBUTE_EMAIL_VALUE,
                self::MODEL_ATTRIBUTE_PASSWORD => 'password',
                self::MODEL_ATTRIBUTE_PASSWORD_CONFIRMATION => 'password',
            ]
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $user
        ];

        $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE))
            ->assertCreated();

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                self::MODEL_ATTRIBUTE_NAME => $user[self::MODEL_ATTRIBUTE_NAME],
                self::MODEL_ATTRIBUTE_EMAIL => $user[self::MODEL_ATTRIBUTE_EMAIL],
            ]
        );
    }

    /** @test */
    public function user_name_is_required()
    {
        $user = array_filter(
            [
                self::MODEL_ATTRIBUTE_NAME => '',
                self::MODEL_ATTRIBUTE_EMAIL => self::MODEL_ATTRIBUTE_EMAIL_VALUE,
                self::MODEL_ATTRIBUTE_PASSWORD => 'password',
                self::MODEL_ATTRIBUTE_PASSWORD_CONFIRMATION => 'password',
            ]
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $user
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => '/data/attributes/name'],
                'detail' => 'The name field is required.'
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $user);
    }

    /** @test */
    public function user_email_is_required()
    {
        $user = array_filter(
            [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_ATTRIBUTE_NAME_VALUE,
                self::MODEL_ATTRIBUTE_EMAIL => '',
                self::MODEL_ATTRIBUTE_PASSWORD => 'password',
                self::MODEL_ATTRIBUTE_PASSWORD_CONFIRMATION => 'password',
            ]
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $user
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => '/data/attributes/email'],
                'detail' => 'The email address field is required.'
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $user);
    }

    /** @test */
    public function user_email_must_be_unique()
    {
        $user = array_filter(
            [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_ATTRIBUTE_NAME_VALUE,
                self::MODEL_ATTRIBUTE_EMAIL => $this->user->email,
                self::MODEL_ATTRIBUTE_PASSWORD => 'password',
                self::MODEL_ATTRIBUTE_PASSWORD_CONFIRMATION => 'password',
            ]
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $user
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => '/data/attributes/email'],
                'detail' => 'The email address has already been taken.'
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $user);
    }

    /** @test */
    public function user_password_is_required()
    {
        $user = array_filter(
            [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_ATTRIBUTE_NAME_VALUE,
                self::MODEL_ATTRIBUTE_EMAIL => self::MODEL_ATTRIBUTE_EMAIL_VALUE,
                self::MODEL_ATTRIBUTE_PASSWORD => '',
                self::MODEL_ATTRIBUTE_PASSWORD_CONFIRMATION => 'password',
            ]
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $user
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertErrors(
            422,
            [
                [
                    'source' => ['pointer' => '/data/attributes/password'],
                    'detail' => 'The password field is required.'
                ],
                [
                    'source' => ['pointer' => '/data/attributes/password_confirmation'],
                    'detail' => 'The password confirmation field must match password.'
                ]
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $user);
    }

    /** @test */
    public function user_password_must_be_confirmed()
    {
        $user = array_filter(
            [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_ATTRIBUTE_NAME_VALUE,
                self::MODEL_ATTRIBUTE_EMAIL => self::MODEL_ATTRIBUTE_EMAIL_VALUE,
                self::MODEL_ATTRIBUTE_PASSWORD => 'password',
                self::MODEL_ATTRIBUTE_PASSWORD_CONFIRMATION => 'password_confirmation',
            ]
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $user
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => '/data/attributes/password_confirmation'],
                'detail' => 'The password confirmation field must match password.'
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $user);
    }

    /** @test */
    public function user_password_must_have_at_least_8_characters()
    {
        $user = array_filter(
            [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_ATTRIBUTE_NAME_VALUE,
                self::MODEL_ATTRIBUTE_EMAIL => self::MODEL_ATTRIBUTE_EMAIL_VALUE,
                self::MODEL_ATTRIBUTE_PASSWORD => 'pass',
                self::MODEL_ATTRIBUTE_PASSWORD_CONFIRMATION => 'pass',
            ]
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $user
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => '/data/attributes/password'],
                'detail' => 'The password field must be at least 8 characters.'
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $user);
    }
}
