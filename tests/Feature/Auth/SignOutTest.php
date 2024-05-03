<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SignOutTest extends TestCase
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
    public function signed_users_can_sign_out()
    {
        $user = User::factory()->create();
        $token = $user->createToken($user->name)->plainTextToken;

        $this->assertNotNull(
            PersonalAccessToken::findToken($token),
            'The plain text token is invalid'
        );

        $response = $this->jsonApi()
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->post(route('v1.users.signout'));

        $response->assertStatus(200);
    }
}
