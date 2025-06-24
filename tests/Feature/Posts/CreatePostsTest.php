<?php

namespace Tests\Feature\Posts;

use App\Models\Post;
use App\Models\User;
use Database\Seeders\permissionsSeeders\PostsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CreatePostsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'posts';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.store';

    const MODEL_ATTRIBUTE_TITLE = 'title';
    const MODEL_ATTRIBUTE_TITLE_POINTER = '/data/attributes/title';
    const MODEL_ATTRIBUTE_TITLE_POINTER_ASSERTION = 'data\/attributes\/title';

    const MODEL_ATTRIBUTE_CONTENT = 'content';
    const MODEL_ATTRIBUTE_CONTENT_POINTER = '/data/attributes/content';
    const MODEL_ATTRIBUTE_CONTENT_POINTER_ASSERTION = 'data\/attributes\/content';

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
    public function guests_users_cannot_create_posts(): void
    {
        $post = array_filter(Post::factory()->raw());

        $response = $this->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withData(
                [
                    'type' => self::MODEL_PLURAL_NAME,
                    'attributes' => $post
                ]
            )
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unauthorized (401)
        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_users_without_permissions_cannot_create_posts(): void
    {
        $user = User::factory()->create()->assignRole('user');

        $post = $this->transformPostData(array_filter(Post::factory()->raw()));

        $response = $this->actingAs($user)
            ->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withData(
                [
                    'type' => self::MODEL_PLURAL_NAME,
                    'attributes' => $post
                ]
            )
            ->withHeader('Authorization', $this->token)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Forbidden (403)
        $response->assertStatus(403);
    }

    /** @test */
    public function authenticated_users_with_permissions_can_create_posts()
    {
        $post = $this->transformPostData(array_filter(Post::factory()->raw()));

        $response = $this->actingAs($this->user)
            ->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withData(
                [
                    'type' => self::MODEL_PLURAL_NAME,
                    'attributes' => $post
                ]
            )
            ->withHeader('Authorization', $this->token)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        $response->assertCreated();

        $this->assertDatabaseHas(
            'posts',
            [
                'title' => $post[self::MODEL_ATTRIBUTE_TITLE],
                'content' => $post[self::MODEL_ATTRIBUTE_CONTENT],
            ]
        );
    }

    /** @test */
    public function posts_title_is_required()
    {
        $post = Post::factory()->raw(
            [
                'title' => ''
            ]
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $this->transformPostData($post)
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withData($data)
            ->withHeader('Authorization', $this->token)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => self::MODEL_ATTRIBUTE_TITLE_POINTER],
                'detail' => 'The title field is required.'
            ]
        );

        $response->assertSee(self::MODEL_ATTRIBUTE_TITLE_POINTER_ASSERTION);

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $post);
    }

    /** @test */
    public function posts_title_must_be_a_string()
    {
        $post = Post::factory()->raw(
            [
                'title' => 123
            ]
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $this->transformPostData($post)
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withData($data)
            ->withHeader('Authorization', $this->token)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => self::MODEL_ATTRIBUTE_TITLE_POINTER],
                'detail' => 'The title field must be a string.'
            ]
        );

        $response->assertSee(self::MODEL_ATTRIBUTE_TITLE_POINTER_ASSERTION);

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $post);
    }

    /** @test */
    public function posts_content_is_required()
    {
        $post = Post::factory()->raw(
            [
                'content' => ''
            ]
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $this->transformPostData($post)
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withData($data)
            ->withHeader('Authorization', $this->token)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => self::MODEL_ATTRIBUTE_CONTENT_POINTER],
                'detail' => 'The content field is required.'
            ]
        );

        $response->assertSee(self::MODEL_ATTRIBUTE_CONTENT_POINTER_ASSERTION);

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $post);
    }

    /** @test */
    public function posts_content_must_be_a_string()
    {
        $post = Post::factory()->raw(
            [
                'content' => 123
            ]
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $this->transformPostData($post)
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withData($data)
            ->withHeader('Authorization', $this->token)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => self::MODEL_ATTRIBUTE_CONTENT_POINTER],
                'detail' => 'The content field must be a string.'
            ]
        );

        $response->assertSee(self::MODEL_ATTRIBUTE_CONTENT_POINTER_ASSERTION);

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $post);
    }

    /**
     * Transform post data from snake_case to camelCase for JSON:API
     *
     * @param array $data
     * @return array
     */
    private function transformPostData(array $data): array
    {
        // Mapping of snake_case to camelCase
        $fieldMap = [
            'published_at' => 'publishedAt',
            'created_at' => 'createdAt',
            'updated_at' => 'updatedAt',
            // Add more fields as needed
        ];

        foreach ($fieldMap as $oldKey => $newKey) {
            if (isset($data[$oldKey])) {
                $data[$newKey] = $data[$oldKey];
                unset($data[$oldKey]);
            }
        }

        return $data;
    }
}
