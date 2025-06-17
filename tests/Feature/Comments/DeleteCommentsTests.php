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

class DeleteCommentsTests extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'comments';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.destroy';

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
    public function guests_users_cannot_delete_comments(): void
    {
        $comment = Comment::factory()->create(
            [
                'post_id' => $this->post->id,
                'user_id' => $this->user->id,
            ]
        );

        $response = $this->jsonApi()
            ->delete(
                route(
                    self::MODEL_MAIN_ACTION_ROUTE,
                    $comment->getRouteKey()
                )
            );

        // Unauthorized (401)
        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_users_as_admin_can_delete_workouts(): void
    {
        $comment = Comment::factory()->create(
            [
                'post_id' => $this->post->id,
                'user_id' => $this->user->id,
            ]
        );

        $response = $this->actingAs($this->user)
            ->jsonApi()
            ->withHeader('Authorization', $this->token)
            ->delete(
                route(
                    self::MODEL_MAIN_ACTION_ROUTE,
                    $comment->getRouteKey()
                )
            );

        // No Content (204)
        $response->assertStatus(204);
        $response->assertNoContent();

        $this->assertDatabaseMissing(
            self::MODEL_PLURAL_NAME,
            [
                'id' => $comment->id,
            ]
        );
    }
}
