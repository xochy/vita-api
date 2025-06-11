<?php

namespace Tests\Feature\Posts;

use App\Models\Post;
use App\Models\User;
use Database\Seeders\permissionsSeeders\PostsPermissionsSeeders;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ListPostsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'posts';
    const MODEL_SHOW_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';
    const MODEL_INDEX_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.index';

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
    public function it_can_fetch_single_post(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $post));

        $response->assertFetchedOne(
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $post->getRouteKey(),
                'attributes' => [
                    'title'       => $post->title,
                    'content'     => $post->content,
                    'publisher'   => $post->user->name,
                    'imageUrl'    => $post->getFirstMediaUrl('images'),
                    'publishedAt' => $post->published_at,
                    'slug'        => $post->slug,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $post)
                ]
            ]
        );
    }

    /** @test */
    public function can_fetch_all_posts(): void
    {
        $posts = Post::factory()->times(3)->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->get(route(self::MODEL_INDEX_ACTION_ROUTE));

        $response->assertFetchedMany(
            $posts->map(function ($post) {
                return [
                    'type' => self::MODEL_PLURAL_NAME,
                    'id' => (string) $post->getRouteKey(),
                    'attributes' => [
                        'title'       => $post->title,
                        'content'     => $post->content,
                        'publisher'   => $post->user->name,
                        'imageUrl'    => $post->getFirstMediaUrl('images'),
                        'publishedAt' => $post->published_at,
                        'slug'        => $post->slug,
                    ],
                    'links' => [
                        'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $post)
                    ]
                ];
            })->toArray()
        );
    }
}
