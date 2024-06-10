<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RefreshTokenTest extends TestCase
{
    use RefreshDatabase;

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
    public function guest_users_cannot_refresh_tokens()
    {
        $data = [
            'type' => 'users',
            'attributes' => [
                'token'       => 'invalid.token',
                'device_name' => 'Android.device',
            ]
        ];

        $response = $this->jsonApi()->withData($data)
            ->post(route('v1.users.refresh'));

        // Wrong request (400)
        $response->assertError(
            400,
            [
                'detail' => __('auth.token_refresh_failed')
            ]
        );
    }

    /** @test */
    public function authenticated_users_can_refresh_tokens()
    {
        $token = $this->user->createToken('test')->plainTextToken;

        $data = [
            'type' => 'users',
            'attributes' => [
                'token'       => $token,
                'device_name' => 'Android.device',

            ]
        ];

        $response = $this->jsonApi()->withData($data)
            ->post(route('v1.users.refresh'));

        $response->assertJson(
            [
                'status' => 200,
                'token'  => $token,
                'name'   => $this->user->name,
                'email'  => $this->user->email,
            ]
        );
    }
}
