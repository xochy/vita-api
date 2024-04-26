<?php

namespace Tests\Feature\Users;

use App\Models\User;
use Database\Seeders\permissionsSeeders\UsersPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ListUsersTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'users';
    const MODEL_SHOW_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';
    const MODEL_INDEX_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.index';

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
    public function it_can_fetch_single_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $user));

        $response->assertFetchedOne($user);

        $response->assertFetchedOne([
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $user->getRouteKey(),
            'attributes' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'links' => [
                'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $user)
            ]
        ]);
    }

    /** @test */
    public function can_fetch_all_users()
    {
        $users = User::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->get(route(self::MODEL_INDEX_ACTION_ROUTE));

        $response->assertFetchedMany([
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $this->user->getRouteKey(),
                'attributes' => [
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $this->user)
                ]
            ],
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $users[0]->getRouteKey(),
                'attributes' => [
                    'name' => $users[0]->name,
                    'email' => $users[0]->email,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $users[0])
                ]
            ],
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $users[1]->getRouteKey(),
                'attributes' => [
                    'name' => $users[1]->name,
                    'email' => $users[1]->email,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $users[1])
                ]
            ],
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $users[2]->getRouteKey(),
                'attributes' => [
                    'name' => $users[2]->name,
                    'email' => $users[2]->email,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $users[2])
                ]
            ],
        ]);
    }
}
