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

class CreateCommentsTests extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'comments';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.store';

    const MODEL_ATTRIBUTE_CONTENT = 'content';
    const MODEL_ATTRIBUTE_CONTENT_POINTER = '/data/attributes/content';
    const MODEL_ATTRIBUTE_CONTENT_POINTER_ASSERTION = 'data\/attributes\/content';

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
    public function guests_users_cannot_create_comments(): void
    {
        $comment = array_filter(Comment::factory()->raw());

        $response = $this->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withData(
                [
                    'type' => self::MODEL_PLURAL_NAME,
                    'attributes' => $comment
                ]
            )
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unauthorized (401)
        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_users_can_create_comments(): void
    {
        $comment = array_filter(Comment::factory()->raw());

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $comment,
            'relationships' => [
                'post' => [
                    'data' => [
                        'type' => 'posts',
                        'id' => (string) $this->post->getRouteKey(),
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        $response->assertCreated();

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                'content' => $comment[self::MODEL_ATTRIBUTE_CONTENT],
                'post_id' => $this->post->id,
                'user_id' => $this->user->id,
            ]
        );
    }

    /** @test */
    public function comment_content_is_required(): void
    {
        $comment = Comment::factory()->raw(
            [
                self::MODEL_ATTRIBUTE_CONTENT => null,
            ]
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $comment,
            'relationships' => [
                'post' => [
                    'data' => [
                        'type' => 'posts',
                        'id' => (string) $this->post->getRouteKey(),
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => '/data/attributes/content'],
                'detail' => 'The content field is required.'
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $comment);
    }

    /** @test */
    public function comment_content_must_be_a_string(): void
    {
        $comment = Comment::factory()->raw(
            [
                self::MODEL_ATTRIBUTE_CONTENT => 12345,
            ]
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $comment,
            'relationships' => [
                'post' => [
                    'data' => [
                        'type' => 'posts',
                        'id' => (string) $this->post->getRouteKey(),
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => self::MODEL_ATTRIBUTE_CONTENT_POINTER],
                'detail' => 'The content field must be a string.'
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $comment);
    }

    /** @test */
    public function comment_content_must_not_exceed_10000_length(): void
    {
        $comment = Comment::factory()->raw(
            [
                self::MODEL_ATTRIBUTE_CONTENT => str_repeat('a', 10001),
            ]
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $comment,
            'relationships' => [
                'post' => [
                    'data' => [
                        'type' => 'posts',
                        'id' => (string) $this->post->getRouteKey(),
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => self::MODEL_ATTRIBUTE_CONTENT_POINTER],
                'detail' => 'The content field must not be greater than 10000 characters.'
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $comment);
    }

    /** @test */
    public function comment_must_belong_to_a_post(): void
    {
        $comment = Comment::factory()->raw();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $comment,
            'relationships' => [
                'post' => [
                    'data' => [
                        'type' => 'posts',
                        'id' => 'non-existing-post-id',
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($this->user)
            ->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Not Found (404)
        $response->assertError(
            404,
            [
                'source' => ['pointer' => '/data/relationships/post'],
                'detail' => 'The related resource does not exist.'
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $comment);
    }

    /** @test */
    public function comment_post_is_required(): void
    {
        $comment = Comment::factory()->raw();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $comment,
        ];

        $response = $this->actingAs($this->user)
            ->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => '/data/relationships/post'],
                'detail' => 'The post field is required.'
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $comment);
    }
}
