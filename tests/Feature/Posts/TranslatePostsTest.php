<?php

namespace Tests\Feature\Posts;

use App\Models\Post;
use App\Models\User;
use Database\Seeders\permissionsSeeders\PostsPermissionsSeeders;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TranslatePostsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'posts';
    const MODEL_SHOW_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';
    const MODEL_ES_TITLE = 'Cómo bajar de peso con ejercicio';
    const MODEL_EN_TITLE = 'How to lose weight with exercise';
    const MODEL_ES_CONTENT = 'Contenido del post en español';
    const MODEL_EN_CONTENT = 'Post content in english';

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
    public function posts_can_have_title_translations()
    {
        $post = Post::factory(
            [
                'title' => self::MODEL_EN_TITLE,
                'content' => self::MODEL_EN_CONTENT,
            ]
        )->hasTranslations(
                1,
                [
                    'locale' => 'es',
                    'column' => 'title',
                    'translation' => self::MODEL_ES_TITLE,
                ]
            )
            ->withoutImage()
            ->create([
                'user_id' => $this->user->id,
            ]);

        // Make a request with spanish locale
        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withHeader('Locale', 'es')
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $post));

        $response->assertJsonFragment([
            'title' => self::MODEL_ES_TITLE,
            'content' => self::MODEL_EN_CONTENT, // Content remains in English
        ]);
    }

    /** @test */
    public function posts_can_have_content_translations()
    {
        $post = Post::factory(
            [
                'title' => self::MODEL_EN_TITLE,
                'content' => self::MODEL_EN_CONTENT,
            ]
        )->hasTranslations(
                1,
                [
                    'locale' => 'es',
                    'column' => 'content',
                    'translation' => self::MODEL_ES_CONTENT,
                ]
            )
            ->withoutImage()
            ->create([
                    'user_id' => $this->user->id,
                ]);

        // Make a request with spanish locale
        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withHeader('Locale', 'es')
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $post));

        $response->assertJsonFragment([
            'title' => self::MODEL_EN_TITLE, // Title remains in English
            'content' => self::MODEL_ES_CONTENT,
        ]);
    }

    /** @test */
    public function posts_can_have_translations_for_its_attributes()
    {
        $post = Post::factory(
            [
                'title' => self::MODEL_EN_TITLE,
                'content' => self::MODEL_EN_CONTENT,
            ]
        )->hasTranslations(
                1,
                [
                    'locale' => 'es',
                    'column' => 'title',
                    'translation' => self::MODEL_ES_TITLE,
                ]
            )->hasTranslations(
                1,
                [
                    'locale' => 'es',
                    'column' => 'content',
                    'translation' => self::MODEL_ES_CONTENT,
                ]
            )
            ->withoutImage()
            ->create([
                    'user_id' => $this->user->id,
                ]);

        // Make a request with spanish locale
        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withHeader('Locale', 'es')
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $post));

        $response->assertJsonFragment([
            'title' => self::MODEL_ES_TITLE,
            'content' => self::MODEL_ES_CONTENT,
        ]);
    }

    /** @test */
    public function translations_can_be_associated_to_posts()
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $data = [
            'type' => 'translations',
            'attributes' => [
                'locale' => 'es',
                'column' => 'name',
                'translation' => self::MODEL_ES_TITLE,
            ],
            'relationships' => [
                'translationable' => [
                    'data' => [
                        'type' => self::MODEL_PLURAL_NAME,
                        'id' => (string) $post->getRouteKey(),
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects('translations')->withData($data)
            ->post(route('v1.translations.store'));

        $response->assertCreated();

        $this->assertDatabaseHas(
            'translations',
            [
                'locale' => 'es',
                'column' => 'name',
                'translation' => self::MODEL_ES_TITLE,
                'translationable_type' => Post::class,
                'translationable_id' => $post->id,
            ]
        );
    }

    /** @test */
    public function post_translations_can_be_updated()
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $translation = $post->translations()->create(
            [
                'locale' => 'es',
                'column' => 'name',
                'translation' => self::MODEL_ES_TITLE,
            ]
        );

        $data = [
            'type' => 'translations',
            'id' => (string) $translation->getRouteKey(),
            'attributes' => [
                'translation' => 'Cómo bajar de peso actualizado',
            ],
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects('translations')->withData($data)
            ->patch(route('v1.translations.update', $translation));

        $response->assertStatus(200);

        $this->assertDatabaseHas(
            'translations',
            [
                'id' => $translation->id,
                'locale' => 'es',
                'column' => 'name',
                'translation' => 'Cómo bajar de peso actualizado',
                'translationable_type' => Post::class,
                'translationable_id' => $post->id,
            ]
        );
    }
}
