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

class FilterPostsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_SINGLE_NAME = 'post';
    const MODEL_PLURAL_NAME = 'posts';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.index';

    const MODEL_BETA_TITLE = 'beta title';
    const MODEL_ALFA_TITLE = 'alfa title';
    const MODEL_GAMA_TITLE = 'gama title';

    const MODEL_GAMA_CONTENT = 'gama content';
    const MODEL_BETA_CONTENT = 'beta content';
    const MODEL_ALFA_CONTENT = 'alfa content';

    const MODEL_PI_TITLE = 'pi lambda title';
    const MODEL_JI_TITLE = 'ji lambda title';

    const MODEL_EXTRA_SEARCHING_TERM = 'omega';
    const MODEL_MULTIPLE_SEARCH_TERM = self::MODEL_SINGLE_NAME . ' ' . 'lambda';

    const MODEL_FILTER_TITLE_PARAM_NAME = 'filter[title]';
    const MODEL_FILTER_SEARCH_PARAM_NAME = 'filter[search]';
    const MODEL_FILTER_UNKNOWN_PARAM_NAME = 'filter[unknown]';
    const MODEL_FILTER_CONTENT_PARAM_NAME = 'filter[content]';

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
    public function can_filter_posts_by_title()
    {
        Post::factory()->count(3)
            ->state(new Sequence(
                [
                    'title' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_TITLE
                ],
                [
                    'title' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_TITLE
                ],
                [
                    'title' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_TITLE
                ],
            ))
            ->create([
                'user_id' => $this->user->id,
            ]);

        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                self::MODEL_FILTER_TITLE_PARAM_NAME => 'alfa'
            ]
        );

        $this->actingAs($this->user)->jsonApi()->get($url)
            ->assertJsonCount(1, 'data')
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_TITLE)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_TITLE)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_TITLE);
    }

    /** @test */
    public function can_filter_posts_by_content()
    {
        Post::factory()->count(3)
            ->state(new Sequence(
                [
                    'content' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_CONTENT
                ],
                [
                    'content' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_CONTENT
                ],
                [
                    'content' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_CONTENT
                ],
            ))
            ->create([
                'user_id' => $this->user->id,
            ]);

        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                self::MODEL_FILTER_CONTENT_PARAM_NAME => 'alfa'
            ]
        );

        $this->actingAs($this->user)->jsonApi()->get($url)
            ->assertJsonCount(1, 'data')
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_CONTENT)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_CONTENT)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_CONTENT);
    }

    /** @test */
    public function can_filter_posts_by_title_and_content()
    {
        Post::factory()->count(3)
            ->state(new Sequence(
                [
                    'title' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_TITLE,
                    'content' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_CONTENT,
                ],
                [
                    'title' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_TITLE,
                    'content' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_CONTENT,
                ],
                [
                    'title' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_TITLE,
                    'content' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_CONTENT,
                ],
            ))
            ->create([
                'user_id' => $this->user->id,
            ]);

        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                self::MODEL_FILTER_TITLE_PARAM_NAME => 'alfa',
                self::MODEL_FILTER_CONTENT_PARAM_NAME => 'alfa',
            ]
        );

        $this->actingAs($this->user)->jsonApi()->get($url)
            ->assertJsonCount(1, 'data')
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_TITLE)
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_CONTENT)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_TITLE)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_CONTENT)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_TITLE)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_CONTENT);
    }

    /** @test */
    public function cannot_filter_posts_by_unknown_filters()
    {
        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                self::MODEL_FILTER_UNKNOWN_PARAM_NAME => 2
            ]
        );

        $response = $this->actingAs($this->user)->jsonApi()
            ->get($url);

        // Bad Request (400)
        $response->assertError(
            400,
            [
                'title' => 'Invalid Query Parameter',
                'detail' => 'Filter parameter unknown is not allowed.',
                'source' => ['parameter' => 'filter'],
            ]
        );

        $response = $this->actingAs($this->user)->jsonApi()
            ->withHeader('Locale', 'es')
            ->get($url);

        // Bad Request
        $response->assertError(
            400,
            [
                'title' => 'Parámetro de Consulta No Válido',
                'detail' => 'El parámetro de fitro unknown no está permido.',
                'source' => ['parameter' => 'filter'],
            ]
        );
    }

    /** @test */
    public function can_search_posts_by_title()
    {
        Post::factory()->count(3)
            ->state(new Sequence(
                [
                    'title' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_TITLE
                ],
                [
                    'title' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_TITLE
                ],
                [
                    'title' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_TITLE
                ],
            ))
            ->create([
                'user_id' => $this->user->id,
            ]);

        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                self::MODEL_FILTER_SEARCH_PARAM_NAME => 'alfa'
            ]
        );

        $this->actingAs($this->user)->jsonApi()->get($url)
            ->assertJsonCount(1, 'data')
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_TITLE)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_TITLE)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_TITLE);
    }

    /** @test */
    public function can_search_posts_by_content()
    {
        Post::factory()->count(3)
            ->state(new Sequence(
                [
                    'content' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_CONTENT
                ],
                [
                    'content' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_CONTENT
                ],
                [
                    'content' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_CONTENT
                ],
            ))
            ->create([
                'user_id' => $this->user->id,
            ]);

        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                self::MODEL_FILTER_SEARCH_PARAM_NAME => 'alfa'
            ]
        );

        $this->actingAs($this->user)->jsonApi()->get($url)
            ->assertJsonCount(1, 'data')
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_CONTENT)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_CONTENT)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_CONTENT);
    }

    /** @test */
    public function can_search_posts_by_title_and_content()
    {
        Post::factory()->count(3)
            ->state(new Sequence(
                [
                    'title' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_TITLE,
                    'content' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_CONTENT,
                ],
                [
                    'title' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_TITLE,
                    'content' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_CONTENT,
                ],
                [
                    'title' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_TITLE,
                    'content' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_CONTENT,
                ],
            ))
            ->create([
                'user_id' => $this->user->id,
            ]);

        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                self::MODEL_FILTER_SEARCH_PARAM_NAME => 'alfa'
            ]
        );

        $this->actingAs($this->user)->jsonApi()->get($url)
            ->assertJsonCount(1, 'data')
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_TITLE)
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_CONTENT)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_TITLE)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_CONTENT)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_TITLE)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_CONTENT);
    }

    /** @test */
    public function can_search_posts_by_title_with_multiple_terms()
    {
        Post::factory()->count(3)
            ->state(
                new Sequence(
                    ['title' => self::MODEL_EXTRA_SEARCHING_TERM . ' ' . self::MODEL_ALFA_TITLE],
                    ['title' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_PI_TITLE],
                    ['title' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_JI_TITLE],
                )
            )
            ->create([
                'user_id' => $this->user->id,
            ]);

        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                self::MODEL_FILTER_SEARCH_PARAM_NAME => self::MODEL_MULTIPLE_SEARCH_TERM
            ]
        );

        $this->actingAs($this->user)->jsonApi()->get($url)
            ->assertJsonCount(2, 'data')
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_PI_TITLE)
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_JI_TITLE)
            ->assertDontSee(self::MODEL_PLURAL_NAME . ' ' . self::MODEL_ALFA_TITLE);
    }
}
