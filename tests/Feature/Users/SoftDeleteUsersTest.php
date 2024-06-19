<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Database\Seeders\permissionsSeeders\UsersPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SoftDeleteUsersTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'users';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.update';
    const MODEL_ATTRIBUTE_DELETED_AT = 'deletedAt';

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(UsersPermissionsSeeder::class);
        }
    }

    /** @test */
    public function guests_users_cannot_soft_delete_users()
    {
        $user = User::factory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $user->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_DELETED_AT => now()->toDateTimeString(),
            ]
        ];

        $response = $this->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $user));

        // Unauthorized (401)
        $response->assertStatus(401);

        $this->assertNotSoftDeleted($user);
    }

    /** @test */
    public function authenticated_users_cannot_soft_delete_other_users()
    {
        $admin = User::factory()->create()->assignRole('admin');
        $user = User::factory()->create()->assignRole('user');

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $user->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_DELETED_AT => now()->toDateTimeString(),
            ]
        ];

        $response = $this->actingAs($admin)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $user));

        // Forbidden (403)
        $response->assertStatus(403);

        $this->assertNotSoftDeleted($user);
    }

    /** @test */
    public function authenticated_users_can_soft_delete_itself()
    {
        $user = User::factory()->create()->assignRole('user');

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $user->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_DELETED_AT => now()->toIso8601String(),
            ]
        ];

        $this->actingAs($user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $user));

        $this->assertSoftDeleted(
            self::MODEL_PLURAL_NAME,
            [
                'id' => $user->getKey()
            ]
        );
    }

    /** @test */
    public function cannot_fetch_soft_deleted_users()
    {
        $user = User::factory()->create()->assignRole('user');

        $this->actingAs($user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->get(route('v1.users.show', $user))
            ->assertFetchedOne($user);

        $date = now();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $user->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_DELETED_AT => $date->toIso8601String(),
            ]
        ];

        $this->actingAs($user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $user));

        $this->assertSoftDeleted(
            self::MODEL_PLURAL_NAME,
            [
                'id' => $user->getRouteKey()
            ]
        );

        $this->assertDatabaseHas(
            'users',
            [
                'id'         => $user->getRouteKey(),
                'deleted_at' => $date->format('Y-m-d H:i:s')
            ]
        );
    }
}
