<?php

namespace Tests\Feature\Categories;

use App\Models\Category;
use App\Models\User;
use Database\Seeders\PermissionsSeeders\CategoriesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TranslateCategoriesTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'categories';
    const MODEL_SHOW_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';
    const MODEL_ES_NAME = 'Espalda baja';
    const MODEL_EN_NAME = 'Lower back';
    const MODEL_ES_DESCRIPTION = 'Descripción de la categoría en español';
    const MODEL_EN_DESCRIPTION = 'Category description in english';

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(CategoriesPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function categories_can_have_name_translations()
    {
        $category = Category::factory(
            [
                'name' => self::MODEL_EN_NAME,
                'description' => self::MODEL_EN_DESCRIPTION,
            ]
        )->hasTranslations(
            1,
            [
                'locale'      => 'es',
                'column'      => 'name',
                'translation' => self::MODEL_ES_NAME,
            ]
        )->create();

        // Make a request with spanish locale
        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withHeader('Locale', 'es')
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $category));

        $response->assertFetchedOne($category);

        $response->assertFetchedOne([
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $category->getRouteKey(),
            'attributes' => [
                'name' => self::MODEL_ES_NAME,
                'description' => $category->description,
            ],
            'links' => [
                'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $category)
            ]
        ]);

        // Check the app localization is set to 'es'
        $this->assertEquals('es', app()->getLocale());
    }

    /** @test */
    public function categories_can_have_description_translations()
    {
        $category = Category::factory(
            [
                'name' => self::MODEL_EN_NAME,
                'description' => self::MODEL_EN_DESCRIPTION,
            ]
        )->hasTranslations(
            1,
            [
                'locale'      => 'es',
                'column'      => 'description',
                'translation' => self::MODEL_ES_DESCRIPTION,
            ]
        )->create();

        // Make a request with spanish locale
        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withHeader('Locale', 'es')
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $category));

        $response->assertFetchedOne($category);

        $response->assertFetchedOne([
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $category->getRouteKey(),
            'attributes' => [
                'name' => $category->name,
                'description' => self::MODEL_ES_DESCRIPTION,
            ],
            'links' => [
                'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $category)
            ]
        ]);

        // Check the app localization is set to 'es'
        $this->assertEquals('es', app()->getLocale());
    }

    /** @test */
    public function categories_can_have_name_and_description_translations()
    {
        $category = Category::factory(
            [
                'name' => self::MODEL_EN_NAME,
                'description' => self::MODEL_EN_DESCRIPTION,
            ]
        )->hasTranslations(
            2,
            new Sequence(
                [
                    'locale'      => 'es',
                    'column'      => 'name',
                    'translation' => self::MODEL_ES_NAME,
                ],
                [
                    'locale'      => 'es',
                    'column'      => 'description',
                    'translation' => self::MODEL_ES_DESCRIPTION,
                ]
            )
        )->create();

        // Make a request with spanish locale
        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withHeader('Locale', 'es')
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $category));

        $response->assertFetchedOne($category);

        $response->assertFetchedOne([
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $category->getRouteKey(),
            'attributes' => [
                'name' => self::MODEL_ES_NAME,
                'description' => self::MODEL_ES_DESCRIPTION,
            ],
            'links' => [
                'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $category)
            ]
        ]);

        // Check the app localization is set to 'es'
        $this->assertEquals('es', app()->getLocale());
    }

    /** @test */
    public function translations_can_be_associated_with_categories()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects('translations')
            ->withData([
                'type' => 'translations',
                'attributes' => [
                    'locale' => 'es',
                    'column' => 'name',
                    'translation' => self::MODEL_ES_NAME,
                ],
                'relationships' => [
                    'translationable' => [
                        'data' => [
                            'type' => self::MODEL_PLURAL_NAME,
                            'id' => (string) $category->getRouteKey(),
                        ]
                    ]
                ]
            ])
            ->post(route('v1.translations.store'));

        $response->assertCreated();

        $this->assertDatabaseHas('translations', [
            'locale'               => 'es',
            'column'               => 'name',
            'translation'          => self::MODEL_ES_NAME,
            'translationable_type' => 'App\\Models\\Category',
            'translationable_id'   => $category->id,
        ]);
    }

    /** @test */
    public function translations_can_be_updated()
    {
        $category = Category::factory()->create();

        $translation = $category->translations()->create([
            'locale'      => 'es',
            'column'      => 'name',
            'translation' => self::MODEL_ES_NAME,
        ]);

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects('translations')
            ->withData([
                'type' => 'translations',
                'id' => (string) $translation->getRouteKey(),
                'attributes' => [
                    'translation' => 'Espalda baja actualizado',
                ]
            ])
            ->patch(route('v1.translations.update', $translation));

        $response->assertStatus(200);

        $this->assertDatabaseHas('translations', [
            'id'                    => $translation->id,
            'locale'                => 'es',
            'column'                => 'name',
            'translation'           => 'Espalda baja actualizado',
            'translationable_type'  => 'App\\Models\\Category',
            'translationable_id'    => $category->id,
        ]);
    }
}
