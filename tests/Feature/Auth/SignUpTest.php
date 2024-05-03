<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SignUpTest extends TestCase
{
    use RefreshDatabase;

    const USER_NAME = 'John Doe';
    const USER_EMAIL = 'mail@mail.com';
    const USER_PASSWORD = 'password';

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
    public function guest_users_can_sign_up()
    {
        $user = array_filter([
            'name'                  => self::USER_NAME,
            'email'                 => self::USER_EMAIL,
            'password'              => self::USER_PASSWORD,
            'password_confirmation' => self::USER_PASSWORD,
        ]);

        $response = $this->jsonApi()
            ->expects('users')
            ->withData([
                'type' => 'users',
                'attributes' => $user
            ])
            ->post(route('v1.users.signup'));

        // Created (201)
        $response->assertStatus(201);
    }

    /** @test */
    public function guest_users_cannot_sign_up_without_name()
    {
        $user = array_filter([
            'email'                 => self::USER_EMAIL,
            'password'              => self::USER_PASSWORD,
            'password_confirmation' => self::USER_PASSWORD
        ]);

        $response = $this->jsonApi()
            ->expects('users')
            ->withData([
                'type' => 'users',
                'attributes' => $user
            ])
            ->post(route('v1.users.signup'));

        // Unprocessable Entity (422)
        $response->assertError(422, [
            'detail' => 'The name field is required.'
        ]);
    }

    /** @test */
    public function guest_users_cannot_sign_up_without_email()
    {
        $user = array_filter([
            'name'                  => self::USER_NAME,
            'password'              => self::USER_PASSWORD,
            'password_confirmation' => self::USER_PASSWORD,
        ]);

        $response = $this->jsonApi()
            ->expects('users')
            ->withData([
                'type' => 'users',
                'attributes' => $user
            ])
            ->post(route('v1.users.signup'));

        // Unprocessable Entity (422)
        $response->assertError(422, [
            'detail' => 'The email field is required.'
        ]);
    }

    /** @test */
    public function guest_users_cannot_sign_up_with_invalid_email()
    {
        $user = array_filter([
            'name'                  => self::USER_NAME,
            'email'                 => 'invalid_email',
            'password'              => self::USER_PASSWORD,
            'password_confirmation' => self::USER_PASSWORD
        ]);

        $response = $this->jsonApi()
            ->expects('users')
            ->withData([
                'type' => 'users',
                'attributes' => $user
            ])
            ->post(route('v1.users.signup'));

        // Unprocessable Entity (422)
        $response->assertError(422, [
            'detail' => 'The email field must be a valid email address.'
        ]);
    }

    /** @test */
    public function guest_users_cannot_sign_up_with_repeated_email()
    {
        $user = array_filter([
            'name'                  => self::USER_NAME,
            'email'                 => $this->user->email,
            'password'              => self::USER_PASSWORD,
            'password_confirmation' => self::USER_PASSWORD
        ]);

        $response = $this->jsonApi()
            ->expects('users')
            ->withData([
                'type' => 'users',
                'attributes' => $user
            ])
            ->post(route('v1.users.signup'));

        // Unprocessable Entity (422)
        $response->assertError(422, [
            'detail' => 'The email has already been taken.'
        ]);
    }

    /** @test */
    public function guest_users_cannot_sign_up_without_password()
    {
        $user = array_filter([
            'name'                  => self::USER_NAME,
            'email'                 => self::USER_EMAIL,
            'password_confirmation' => self::USER_PASSWORD
        ]);

        $response = $this->jsonApi()
            ->expects('users')
            ->withData([
                'type' => 'users',
                'attributes' => $user
            ])
            ->post(route('v1.users.signup'));

        // Unprocessable Entity (422)
        $response->assertError(422, [
            'detail' => 'The password field is required.'
        ]);
    }

    /** @test */
    public function guest_users_cannot_sign_up_without_password_confirmation()
    {
        $user = array_filter([
            'name'     => self::USER_NAME,
            'email'    => self::USER_EMAIL,
            'password' => self::USER_PASSWORD
        ]);

        $response = $this->jsonApi()
            ->expects('users')
            ->withData([
                'type' => 'users',
                'attributes' => $user
            ])
            ->post(route('v1.users.signup'));

        // Unprocessable Entity (422)
        $response->assertError(422, [
            'detail' => 'The password field confirmation does not match.'
        ]);
    }
}
