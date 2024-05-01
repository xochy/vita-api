<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function cannot_sign_in_with_invalid_credentials()
    {
        User::factory()->create();

        $response = $this->jsonApi()
            ->expects('users')
            ->withData([
                'type' => 'users',
                'attributes' => [
                    'email'       => 'invalid.email@mail.com',
                    'device_name' => 'Android.evice',
                    'password'    => 'invalid.password',
                ]
            ])
            ->post(route('v1.users.signin'));

        // Wrong request (400)
        $response->assertError(400, [
            'detail' => 'These credentials do not match our records.'
        ]);
    }

    /** @test */
    public function email_is_required_for_sign_in()
    {
        $response = $this->jsonApi()
            ->expects('users')
            ->withData([
                'type' => 'users',
                'attributes' => [
                    'device_name' => 'Android.device',
                    'password'    => 'password',
                ]
            ])
            ->post(route('v1.users.signin'));

        // Wrong request (400)
        $response->assertError(400, [
            'detail' => self::FIELDS_VALIDATIONS_MESSAGE
        ]);
    }

    /** @test */
    public function device_name_is_required_for_sign_in()
    {
        $response = $this->jsonApi()
            ->expects('users')
            ->withData([
                'type' => 'users',
                'attributes' => [
                    'email'    => 'device_email@mail.com',
                    'password' => 'password',
                ]
            ])
            ->post(route('v1.users.signin'));

        // Wrong request (400)
        $response->assertError(400, [
            'detail' => self::FIELDS_VALIDATIONS_MESSAGE
        ]);
    }

    /** @test */
    public function password_is_required_for_sign_in()
    {
        $response = $this->jsonApi()
            ->expects('users')
            ->withData([
                'type' => 'users',
                'attributes' => [
                    'email'       => 'testEmail@mail.com',
                    'device_name' => 'AndroidDevice',
                ]
            ])
            ->post(route('v1.users.signin'));

        // Wrong request (400)
        $response->assertError(400, [
            'detail' => self::FIELDS_VALIDATIONS_MESSAGE
        ]);
    }

    /** @test */
    public function email_must_be_valid_for_sign_in()
    {
        $response = $this->jsonApi()
            ->expects('users')
            ->withData([
                'type' => 'users',
                'attributes' => [
                    'email'       => 'invalid_email',
                    'device_name' => 'AndroidDevice',
                    'password'    => 'password',
                ]
            ])
            ->post(route('v1.users.signin'));

        // Wrong request (400)
        $response->assertError(400, [
            'detail' => 'The email field must be a valid email address.'
        ]);
    }

    /** @test */
    public function users_can_sign_in_with_valid_credentials()
    {
        $response = $this->jsonApi()
            ->expects('users')
            ->withData([
                'type' => 'users',
                'attributes' => [
                    'email'       => $this->user->email,
                    'device_name' => 'android.device',
                    'password'    => 'password',
                ]
            ])
            ->post(route('v1.users.signin'));

        $token = $response->json('token');

        $this->assertNotNull(
            PersonalAccessToken::findToken($token),
            'The plain text token is invalid'
        );

        // Success (200)
        $response->assertStatus(200)
            ->assertJson([
                'status' => 200,
                'token'  => $token,
            ]);
    }

    /** @test */
    public function users_cannot_sign_in_twice()
    {
        $user = User::factory()->create();
        $token = $user->createToken($user->name)->plainTextToken;

        $this->assertNotNull(
            PersonalAccessToken::findToken($token),
            'The plain text token is invalid'
        );

        $response = $this->jsonApi()
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->expects('users')
            ->withData([
                'type' => 'users',
                'attributes' => [
                    'email'       => $user->email,
                    'device_name' => 'android.device',
                    'password'    => $user->password,
                ]
            ])
            ->post(route('v1.users.signin'));

        $response->assertStatus(400);
    }
}
