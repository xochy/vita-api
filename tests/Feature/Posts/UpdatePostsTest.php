<?php

namespace Tests\Feature\Posts;

use App\Models\Post;
use App\Models\User;
use Database\Seeders\permissionsSeeders\PostsPermissionsSeeders;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UpdatePostsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'posts';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.update';

    const MODEL_ATTRIBUTE_TITLE = 'title';
    const MODEL_ATTRIBUTCONTENT = 'content';

    const MODEL_TITLE_ATTRIBUTE_VALUE = 'tittle changed';
    const MODEL_CONTENT_ATTRIBUTE_VALUE = 'content changed';

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(PostsPermissionsSeeders::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function guests_users_cannot_update_posts()
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $post->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_TITLE => self::MODEL_TITLE_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTCONTENT => self::MODEL_CONTENT_ATTRIBUTE_VALUE,
            ]
        ];

        $response = $this->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $post->getRouteKey()));

        // Unauthorized (401)
        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_users_as_admin_can_update_posts()
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $post->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_TITLE => self::MODEL_TITLE_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTCONTENT => self::MODEL_CONTENT_ATTRIBUTE_VALUE,
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $post->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                'id' => $post->getRouteKey(),
                self::MODEL_ATTRIBUTE_TITLE => self::MODEL_TITLE_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTCONTENT => self::MODEL_CONTENT_ATTRIBUTE_VALUE,
            ]
        );
    }

    /** @test */
    public function authenticated_users_as_admin_cannot_update_other_users_posts()
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $post->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_TITLE => self::MODEL_TITLE_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTCONTENT => self::MODEL_CONTENT_ATTRIBUTE_VALUE,
            ]
        ];

        $admin = User::factory()->create()->assignRole('admin');

        $response = $this->actingAs($admin)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $post->getRouteKey()));

        // Forbidden (403)
        $response->assertStatus(403);
    }

    /** @test */
    public function can_update_the_posts_title_only()
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $post->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_TITLE => self::MODEL_TITLE_ATTRIBUTE_VALUE,
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $post->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                'id' => $post->getRouteKey(),
                self::MODEL_ATTRIBUTE_TITLE => self::MODEL_TITLE_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTCONTENT => $post->content, // Content should remain unchanged
            ]
        );
    }

    /** @test */
    public function can_update_the_posts_content_only()
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $post->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTCONTENT => self::MODEL_CONTENT_ATTRIBUTE_VALUE,
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $post->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                'id' => $post->getRouteKey(),
                self::MODEL_ATTRIBUTE_TITLE => $post->title, // Title should remain unchanged
                self::MODEL_ATTRIBUTCONTENT => self::MODEL_CONTENT_ATTRIBUTE_VALUE,
            ]
        );
    }

    /** @test */
    public function cannot_update_posts_with_invalid_data()
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $post->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_TITLE => '', // Invalid title
                self::MODEL_ATTRIBUTCONTENT => self::MODEL_CONTENT_ATTRIBUTE_VALUE,
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $post->getRouteKey()));

        // Unprocessable Entity (422)
        $response->assertStatus(422);
    }

    /** @test */
    public function cannot_update_the_post_title_if_exists()
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
        ]);
        
        $post2 = Post::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $post->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_TITLE => $post2->title, // Trying to set an existing title
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $post->getRouteKey()));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => '/data/attributes/title'],
                'detail' => 'The title has already been taken.'
            ]
        );
    }
}
