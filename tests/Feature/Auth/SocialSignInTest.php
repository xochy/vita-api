<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\FirebaseService;
use Database\Seeders\permissionsSeeders\UsersPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\PersonalAccessToken;
use Mockery;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SocialSignInTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $firebaseServiceMock;

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(UsersPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');

        // Mock the FirebaseService
        $this->firebaseServiceMock = Mockery::mock(FirebaseService::class);
        $this->app->instance(FirebaseService::class, $this->firebaseServiceMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function users_can_sign_in_with_google_for_new_user()
    {
        $firebaseUserData = [
            'uid'            => 'firebase_uid_123',
            'email'          => 'john@example.com',
            'name'           => 'John Doe',
            'email_verified' => true,
            'picture'        => 'https://example.com/avatar.jpg'
        ];

        $this->firebaseServiceMock
            ->shouldReceive('verifyIdToken')
            ->once()
            ->with('valid_google_token')
            ->andReturn($firebaseUserData);

        $data = [
            'type' => 'users',
            'attributes' => [
                'firebase_token' => 'valid_google_token',
                'provider'       => 'google',
                'device_name'    => 'Android.device',
            ]
        ];

        $response = $this->jsonApi()
            ->expects('users')->withData($data)
            ->post(route('v1.users.socialSignin'));

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
                    'token' => $token,
                ]
            );

        // Check if the user was created in the database
        $this->assertDatabaseHas('users', [
            'email'        => 'john@example.com',
            'name'         => 'John Doe',
            'firebase_uid' => 'firebase_uid_123',
            'provider'     => 'google'
        ]);
    }

    /** @test */
    public function users_can_sign_in_with_facebook_for_new_user()
    {
        $firebaseUserData = [
            'uid'            => 'firebase_uid_456',
            'email'          => 'jane@example.com',
            'name'           => 'Jane Smith',
            'email_verified' => false,
            'picture'        => 'https://facebook.com/avatar.jpg'
        ];

        $this->firebaseServiceMock
            ->shouldReceive('verifyIdToken')
            ->once()
            ->with('valid_facebook_token')
            ->andReturn($firebaseUserData);

            $data = [
            'type' => 'users',
            'attributes' => [
                'firebase_token' => 'valid_facebook_token',
                'provider'       => 'facebook',
                'device_name'    => 'Android.device',
            ]
        ];

        $response = $this->jsonApi()
            ->expects('users')->withData($data)
            ->post(route('v1.users.socialSignin'));

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
                    'token' => $token,
                ]
            );

        // Check if the user was created in the database
        $this->assertDatabaseHas('users', [
            'email'        => 'jane@example.com',
            'name'         => 'Jane Smith',
            'firebase_uid' => 'firebase_uid_456',
            'provider'     => 'facebook'
        ]);
    }

    /** @test */
    public function can_sign_in_existing_user_by_firebase_uid()
    {
        $existingUser = User::factory()->create([
            'firebase_uid' => 'existing_firebase_uid',
            'email'        => 'existing@example.com',
            'name'         => 'Existing User'
        ]);

        $firebaseUserData = [
            'uid'            => 'existing_firebase_uid',
            'email'          => 'existing@example.com',
            'name'           => 'Existing User Updated',
            'email_verified' => true
        ];

        $this->firebaseServiceMock
            ->shouldReceive('verifyIdToken')
            ->once()
            ->with('existing_user_token')
            ->andReturn($firebaseUserData);

        $data = [
            'type' => 'users',
            'attributes' => [
                'firebase_token' => 'existing_user_token',
                'provider'       => 'google',
                'device_name'    => 'Android.device',
            ]
        ];

        $response = $this->jsonApi()
            ->expects('users')->withData($data)
            ->post(route('v1.users.socialSignin'));

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

        $responseData = $response->json();

        // Check if the response contains the existing user's data
        $this->assertEquals($existingUser->name, $responseData['name']);
        $this->assertEquals($existingUser->email, $responseData['email']);
    }

    /** @test */
    public function it_can_link_firebase_uid_to_existing_user_by_email()
    {
        $existingUser = User::factory()->create([
            'email' => 'linkuser@example.com',
            'firebase_uid' => null
        ]);

        $firebaseUserData = [
            'uid'            => 'new_firebase_uid',
            'email'          => 'linkuser@example.com',
            'name'           => 'Link User',
            'email_verified' => true
        ];

        $this->firebaseServiceMock
            ->shouldReceive('verifyIdToken')
            ->once()
            ->with('link_token')
            ->andReturn($firebaseUserData);

        $data = [
            'type' => 'users',
            'attributes' => [
                'firebase_token' => 'link_token',
                'provider'       => 'google',
                'device_name'    => 'Android.device',
            ]
        ];

        $response = $this->jsonApi()
            ->expects('users')->withData($data)
            ->post(route('v1.users.socialSignin'));

        $response->assertStatus(200);

        $existingUser->refresh();
        $this->assertEquals('new_firebase_uid', $existingUser->firebase_uid);

        $responseData = $response->json();

        // Check if the response contains the updated user's data
        $this->assertEquals($existingUser->name, $responseData['name']);
        $this->assertEquals($existingUser->email, $responseData['email']);
    }

    /** @test */
    public function it_fails_with_invalid_firebase_token()
    {
        $this->firebaseServiceMock
            ->shouldReceive('verifyIdToken')
            ->once()
            ->with('invalid_token');

        $data = [
            'type' => 'users',
            'attributes' => [
                'firebase_token' => 'invalid_token',
                'provider'       => 'google',
                'device_name'    => 'Android.device',
            ]
        ];

        $response = $this->jsonApi()
            ->expects('users')->withData($data)
            ->post(route('v1.users.socialSignin'));

        // Assert that the response status is 422 (Validation Error)
        $response->assertError(
            422,
            [
                'detail' => __('exceptions.invalid_firebase_token')
            ]
        );
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $data = [
            'type' => 'users',
            'attributes' => [
                'firebase_token' => '',
                'provider'       => '',
                'device_name'    => '',
            ]
        ];

        $response = $this->jsonApi()
            ->expects('users')->withData($data)
            ->withHeader('Locale', 'en')
            ->post(route('v1.users.socialSignin'));

        // Assert that the response status is 422 (Validation Error)
        $response->assertError(
            422,
            [
                'detail' => 'The Firebase token field is required.'
            ]
        );
    }

    /** @test */
    public function it_validates_provider_values()
    {
        $data = [
            'type' => 'users',
            'attributes' => [
                'firebase_token' => 'valid_token',
                'provider'       => 'invalid_provider',
                'device_name'    => 'Android.device',
            ]
        ];

        $response = $this->jsonApi()
            ->expects('users')->withData($data)
            ->post(route('v1.users.socialSignin'));

        // Assert that the response status is 422 (Validation Error)
        $response->assertError(
            422,
            [
                'detail' => 'The provided Firebase token is invalid or has expired.'
            ]
        );
    }
}
