<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\permissionsSeeders\UsersPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Spatie\Permission\Models\Role;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class SignInTest extends TestCase
{
    use RefreshDatabase;

    const FIELDS_VALIDATIONS_MESSAGE = 'One or more required fields are missing.';

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
    public function cannot_sign_in_with_invalid_credentials()
    {
        User::factory()->create();

        $data = [
            'type' => 'users',
            'attributes' => [
                'email'       => 'invalid.email@mail.com',
                'device_name' => 'Android.device',
                'password'    => 'invalid.password',
            ]
        ];

        $response = $this->jsonApi()
            ->expects('users')->withData($data)
            ->withHeader('Locale', 'es')
            ->post(route('v1.users.signin'));

        // Wrong request (400)
        $response->assertError(
            400,
            [
                'detail' => __('auth.failed')
            ]
        );
    }

    /** @test */
    public function email_is_required_for_sign_in()
    {
        $data = [
            'type' => 'users',
            'attributes' => [
                'device_name' => 'Android.device',
                'password'    => 'password',
            ]
        ];

        $response = $this->jsonApi()
            ->expects('users')->withData($data)
            ->withHeader('Locale', 'es')
            ->post(route('v1.users.signin'));

        // Wrong request (400)
        $response->assertError(
            400,
            [
                'detail' => __('auth.required')
            ]
        );
    }

    /** @test */
    public function device_name_is_required_for_sign_in()
    {
        $data = [
            'type' => 'users',
            'attributes' => [
                'email'    => 'device_email@mail.com',
                'password' => 'password',
            ]
        ];

        $response = $this->jsonApi()
            ->expects('users')->withData($data)
            ->post(route('v1.users.signin'));

        // Wrong request (400)
        $response->assertError(
            400,
            [
                'detail' => __('auth.required')
            ]
        );
    }

    /** @test */
    public function password_is_required_for_sign_in()
    {
        $data = [
            'type' => 'users',
            'attributes' => [
                'email'       => 'testEmail@mail.com',
                'device_name' => 'AndroidDevice',
            ]
        ];

        $response = $this->jsonApi()
            ->expects('users')->withData($data)
            ->post(route('v1.users.signin'));

        // Wrong request (400)
        $response->assertError(
            400,
            [
                'detail' => __('auth.required')
            ]
        );
    }

    /** @test */
    public function email_must_be_valid_for_sign_in()
    {
        $data = [
            'type' => 'users',
            'attributes' => [
                'email'       => 'invalid_email',
                'device_name' => 'AndroidDevice',
                'password'    => 'password',
            ]
        ];

        $response = $this->jsonApi()
            ->expects('users')->withData($data)
            ->withHeader('Locale', 'es')
            ->post(route('v1.users.signin'));

        // Wrong request (400)
        $response->assertError(
            400,
            [
                'detail' => __('validation.email', [
                    'attribute' => __('validation.attributes.email')
                ])
            ]
        );
    }

    /** @test */
    public function users_can_sign_in_with_valid_credentials()
    {
        $data = [
            'type' => 'users',
            'attributes' => [
                'email'       => $this->user->email,
                'device_name' => 'android.device',
                'password'    => 'password',
            ]
        ];

        $response = $this->jsonApi()
            ->expects('users')->withData($data)
            ->post(route('v1.users.signin'));

        $token = $response->json('token');

        $this->assertNotNull(
            PersonalAccessToken::findToken($token),
            __('auth.token')
        );

        // Success (200)
        $response->assertStatus(200)
            ->assertJson(
                [
                    'status' => 200,
                    'token'  => $token,
                ]
            );
    }

    /** @test */
    public function users_cannot_sign_in_twice()
    {
        $user = User::factory()->create();
        $token = $user->createToken($user->name)->plainTextToken;

        $data = [
            'type' => 'users',
            'attributes' => [
                'email'       => $user->email,
                'device_name' => 'android.device',
                'password'    => $user->password,
            ]
        ];

        $this->assertNotNull(
            PersonalAccessToken::findToken($token),
            __('auth.token')
        );

        $response = $this->jsonApi()
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->expects('users')->withData($data)
            ->post(route('v1.users.signin'));

        // Wrong request (400)
        $response->assertError(
            400,
            [
                'detail' => __('auth.failed')
            ]
        );
    }

    /** @test */
    public function sign_in_response_can_have_permissions()
    {
        $data = [
            'type' => 'users',
            'attributes' => [
                'email'       => $this->user->email,
                'device_name' => 'android.device',
                'password'    => 'password',
            ]
        ];

        $response = $this->jsonApi()
            ->expects('users')->withData($data)
            ->post(route('v1.users.signin'));

        $permissionsPayload = $response->json('permissions');
        $permissions = decryptPayload($permissionsPayload);

        $this->assertTrue(
            $this->user->can(json_decode($permissions))
        );
    }
}
