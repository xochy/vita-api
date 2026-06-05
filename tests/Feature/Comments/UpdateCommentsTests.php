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

class UpdateCommentsTests extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'comments';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.update';

    const MODEL_ATTRIBUTE_CONTENT = 'content';
    const MODEL_CONTENT_ATTRIBUTE_VALUE = 'name changed';

    protected User $user;
    protected Post $post;
    protected string $token;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(CommentsPermissionsSeeders::class);
        }

        [$this->user, $this->token] = $this->createUserWithToken();

        $this->post = Post::factory()->create([
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function guests_users_cannot_update_comments(): void
    {
        $comment = Comment::factory()->create(
            [
                'post_id' => (string) $this->post->id,
                'user_id' => (string) $this->user->id,
            ]
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $comment->id,
            'attributes' => [
                self::MODEL_ATTRIBUTE_CONTENT => self::MODEL_CONTENT_ATTRIBUTE_VALUE,
            ],
        ];

        $response = $this->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $comment->getRouteKey()));

        // Unauthorized (401)
        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_users_can_update_comments(): void
    {
        $comment = Comment::factory()->create(
            [
                'post_id' => $this->post->id,
                'user_id' => $this->user->id,
            ]
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $comment->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_CONTENT => self::MODEL_CONTENT_ATTRIBUTE_VALUE,
            ],
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withData($data)
            ->withHeader('Authorization', $this->token)
            ->patch(
                route(
                    self::MODEL_MAIN_ACTION_ROUTE,
                    $comment->getRouteKey()
                )
            );

        // OK (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                'id' => (string) $comment->id,
                self::MODEL_ATTRIBUTE_CONTENT => self::MODEL_CONTENT_ATTRIBUTE_VALUE,
                'post_id' => (string) $this->post->id,
                'user_id' => (string) $this->user->id,
            ]
        );
    }
}
