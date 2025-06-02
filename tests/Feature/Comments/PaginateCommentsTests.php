<?php

namespace Tests\Feature\Comments;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\permissionsSeeders\CommentsPermissionsSeeders;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaginateCommentsTests extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'comments';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.index';

    const MODEL_SIZE_PARAM_NAME = 'page[size]';
    const MODEL_NUMBER_PARAM_NAME = 'page[number]';

    protected User $user;
    protected Post $post;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(CommentsPermissionsSeeders::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
        $this->post = Post::factory()->create([
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function can_fetch_paginated_comments()
    {
        Comment::factory()->times(10)->create([
            'post_id' => $this->post->id,
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
            ->expects(self::MODEL_NUMBER_PARAM_NAME)->get($url);

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
                )
            ]
        );
    }
}
