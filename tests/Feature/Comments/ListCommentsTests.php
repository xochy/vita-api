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

class ListCommentsTests extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'comments';
    const MODEL_SHOW_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';
    const MODEL_INDEX_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.index';

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
    public function it_cait_can_fetch_single_comment(): void
    {
        $comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $comment));

        $response->assertFetchedOne(
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $comment->getRouteKey(),
                'attributes' => [
                    'content' => $comment->content,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $comment)
                ]
            ]
        );
    }

    /** @test */
    public function can_fetch_all_posts(): void
    {
        $comments = Comment::factory()->times(3)->create([
            'post_id' => $this->post->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->get(route(self::MODEL_INDEX_ACTION_ROUTE));

        $response->assertFetchedMany(
            $comments->map(function ($comment) {
                return [
                    'type' => self::MODEL_PLURAL_NAME,
                    'id' => (string) $comment->getRouteKey(),
                    'attributes' => [
                        'content' => $comment->content,
                    ],
                    'links' => [
                        'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $comment)
                    ]
                ];
            })->toArray()
        );
    }
}
