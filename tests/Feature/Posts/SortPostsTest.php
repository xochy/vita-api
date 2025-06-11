<?php

namespace Tests\Feature\Posts;

use App\Models\Post;
use App\Models\User;
use Database\Seeders\permissionsSeeders\PostsPermissionsSeeders;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SortPostsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'posts';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.index';

    const MODEL_ALFA_TITLE = 'alfa title';
    const MODEL_BETA_TITLE = 'beta title';
    const MODEL_GAMA_TITLE = 'gama title';

    const MODEL_TITLE_PARAM_VALUE = 'title';
    const MODEL_USER_ID_PARAM_VALUE = 'user_id';
    const MODEL_SORT_PARAM_VALUE = 'published_at';

    const SORT_PARAM_VALUE = 'publishedAt';

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
    public function can_sort_posts_by_published_at()
    {
        Post::factory()->count(3)
            ->state(new Sequence(
                [
                    self::MODEL_USER_ID_PARAM_VALUE => $this->user->id,
                    self::MODEL_TITLE_PARAM_VALUE => self::MODEL_GAMA_TITLE,
                    self::MODEL_SORT_PARAM_VALUE => now()->addDays(3)
                ],
                [
                    self::MODEL_USER_ID_PARAM_VALUE => $this->user->id,
                    self::MODEL_TITLE_PARAM_VALUE => self::MODEL_ALFA_TITLE,
                    self::MODEL_SORT_PARAM_VALUE => now()->addDays(1)
                ],
                [
                    self::MODEL_USER_ID_PARAM_VALUE => $this->user->id,
                    self::MODEL_TITLE_PARAM_VALUE => self::MODEL_BETA_TITLE,
                    self::MODEL_SORT_PARAM_VALUE => now()->addDays(2)
                ],
            ))
            ->withoutImage()
            ->create();

        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                'sort' => self::SORT_PARAM_VALUE
            ]
        );

        $this->actingAs($this->user)->jsonApi()
            ->get($url)->assertSeeInOrder(
                [
                    self::MODEL_ALFA_TITLE,
                    self::MODEL_BETA_TITLE,
                    self::MODEL_GAMA_TITLE,
                ]
            );
    }

    /** @test */
    public function can_sort_posts_by_published_at_desc()
    {
        Post::factory()->count(3)
            ->state(new Sequence(
                [
                    self::MODEL_USER_ID_PARAM_VALUE => $this->user->id,
                    self::MODEL_TITLE_PARAM_VALUE => self::MODEL_GAMA_TITLE,
                    self::MODEL_SORT_PARAM_VALUE => now()->addDays(3)
                ],
                [
                    self::MODEL_USER_ID_PARAM_VALUE => $this->user->id,
                    self::MODEL_TITLE_PARAM_VALUE => self::MODEL_ALFA_TITLE,
                    self::MODEL_SORT_PARAM_VALUE => now()->addDays(1)
                ],
                [
                    self::MODEL_USER_ID_PARAM_VALUE => $this->user->id,
                    self::MODEL_TITLE_PARAM_VALUE => self::MODEL_BETA_TITLE,
                    self::MODEL_SORT_PARAM_VALUE => now()->addDays(2)
                ],
            ))
            ->withoutImage()
            ->create();

        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                'sort' => '-' . self::SORT_PARAM_VALUE
            ]
        );

        $this->actingAs($this->user)->jsonApi()
            ->get($url)->assertSeeInOrder(
                [
                    self::MODEL_GAMA_TITLE,
                    self::MODEL_BETA_TITLE,
                    self::MODEL_ALFA_TITLE,
                ]
            );
    }
}
