<?php

namespace Tests\Feature\Subcategories;

use App\Models\Subcategory;
use App\Models\User;
use Database\Seeders\permissionsSeeders\SubcategoriesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TranslateSubcategoryTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'subcategories';
    const MODEL_SHOW_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';
    const MODEL_ES_NAME = 'Espalda baja';
    const MODEL_EN_NAME = 'Lower back';
    const MODEL_ES_DESCRIPTION = 'Descripción de la subcategoría en español';
    const MODEL_EN_DESCRIPTION = 'Category description in english';

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(SubcategoriesPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function subcategories_can_have_name_translations()
    {
        $subcategory = Subcategory::factory(
            [
                'name' => self::MODEL_EN_NAME,
                'description' => self::MODEL_EN_DESCRIPTION,
            ]
        )
            ->forCategory()
            ->hasTranslations(
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
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $subcategory));

        $response->assertFetchedOne($subcategory);

        $response->assertFetchedOne(
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $subcategory->getRouteKey(),
                'attributes' => [
                    'name'        => self::MODEL_ES_NAME,
                    'description' => $subcategory->description,
                    'slug'        => $subcategory->slug,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $subcategory)
                ]
            ]
        );

        // Check the app localization is set to 'es'
        $this->assertEquals('es', app()->getLocale());
    }

    /** @test */
    public function subcategories_can_have_description_translations()
    {
        $subcategory = Subcategory::factory(
            [
                'name' => self::MODEL_EN_NAME,
                'description' => self::MODEL_EN_DESCRIPTION,
            ]
        )
            ->forCategory()
            ->hasTranslations(
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
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $subcategory));

        $response->assertFetchedOne($subcategory);

        $response->assertFetchedOne(
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $subcategory->getRouteKey(),
                'attributes' => [
                    'name'        => $subcategory->name,
                    'description' => self::MODEL_ES_DESCRIPTION,
                    'slug'        => $subcategory->slug,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $subcategory)
                ]
            ]
        );

        // Check the app localization is set to 'es'
        $this->assertEquals('es', app()->getLocale());
    }

    /** @test */
    public function subcategories_can_have_translations_for_its_attributes()
    {
        $subcategory = Subcategory::factory(
            [
                'name' => self::MODEL_EN_NAME,
                'description' => self::MODEL_EN_DESCRIPTION,
            ]
        )
            ->forCategory()
            ->hasTranslations(
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
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $subcategory));

        $response->assertFetchedOne($subcategory);

        $response->assertFetchedOne(
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $subcategory->getRouteKey(),
                'attributes' => [
                    'name'        => self::MODEL_ES_NAME,
                    'description' => self::MODEL_ES_DESCRIPTION,
                    'slug'        => $subcategory->slug,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $subcategory)
                ]
            ]
        );

        // Check the app localization is set to 'es'
        $this->assertEquals('es', app()->getLocale());
    }

    /** @test */
    public function translations_can_be_associated_with_subcategories()
    {
        $subcategory = Subcategory::factory()->forCategory()->create();

        $data = [
            'type' => 'translations',
            'attributes' => [
                'locale' => 'es',
                'column' => 'name',
                'translation' => self::MODEL_ES_NAME,
            ],
            'relationships' => [
                'translationable' => [
                    'data' => [
                        'type' => 'subcategories',
                        'id' => (string) $subcategory->getRouteKey(),
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
                'locale'               => 'es',
                'column'               => 'name',
                'translation'          => self::MODEL_ES_NAME,
                'translationable_type' => 'App\\Models\\Subcategory',
                'translationable_id'   => $subcategory->id,
            ]
        );
    }

    /** @test */
    public function subcategories_translations_can_be_updated()
    {
        $subcategory = Subcategory::factory()->forCategory()->create();

        $translation = $subcategory->translations()->create(
            [
                'locale'      => 'es',
                'column'      => 'name',
                'translation' => self::MODEL_ES_NAME,
            ]
        );

        $data = [
            'type' => 'translations',
            'id' => (string) $translation->getRouteKey(),
            'attributes' => [
                'translation' => 'Espalda baja actualizado',
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects('translations')
            ->withData($data)
            ->patch(route('v1.translations.update', $translation));

        $response->assertStatus(200);

        $this->assertDatabaseHas(
            'translations',
            [
                'locale'               => 'es',
                'column'               => 'name',
                'translation'          => 'Espalda baja actualizado',
                'translationable_type' => 'App\\Models\\Subcategory',
                'translationable_id'   => $subcategory->id,
            ]
        );
    }
}
