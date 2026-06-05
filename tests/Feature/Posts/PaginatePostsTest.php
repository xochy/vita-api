<?php

namespace Tests\Feature\Posts;

use App\Models\Post;
use App\Models\User;
use Database\Seeders\permissionsSeeders\PostsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaginatePostsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'posts';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.index';

    const MODEL_SIZE_PARAM_NAME = 'page[size]';
    const MODEL_NUMBER_PARAM_NAME = 'page[number]';

    protected User $user;
    protected string $token;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(PostsPermissionsSeeder::class);
        }

        [$this->user, $this->token] = $this->createUserWithToken();
    }

    /** @test */
    public function can_fetch_paginated_posts()
    {
        Post::factory()->times(10)->withoutImage()->create([
            'user_id' => $this->user->id,
        ]);

        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                self::MODEL_SIZE_PARAM_NAME => 2,
                self::MODEL_NUMBER_PARAM_NAME => 3
            ]
        );

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_NUMBER_PARAM_NAME)
            ->withHeader('Authorization', $this->token)
            ->get($url);

        $response->assertJsonStructure(
            [
                'links' => ['first', 'prev', 'next', 'last']
            ]
        );

        $response->assertJsonFragment(
            [
                'first' => route(
                    self::MODEL_MAIN_ACTION_ROUTE,
                    [
                        self::MODEL_NUMBER_PARAM_NAME => 1,
                        self::MODEL_SIZE_PARAM_NAME => 2
                    ]
                ),
                'prev' => route(
                    self::MODEL_MAIN_ACTION_ROUTE,
                    [
                        self::MODEL_NUMBER_PARAM_NAME => 2,
                        self::MODEL_SIZE_PARAM_NAME => 2
                    ]
                ),
                'next' => route(
                    self::MODEL_MAIN_ACTION_ROUTE,
                    [
                        self::MODEL_NUMBER_PARAM_NAME => 4,
                        self::MODEL_SIZE_PARAM_NAME => 2
                    ]
                ),
                'last' => route(
                    self::MODEL_MAIN_ACTION_ROUTE,
                    [
                        self::MODEL_NUMBER_PARAM_NAME => 5,
                        self::MODEL_SIZE_PARAM_NAME => 2
                    ]
                ),
            ]
        );
    }
}
