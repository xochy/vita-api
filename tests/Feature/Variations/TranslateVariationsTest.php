<?php

namespace Tests\Feature\Variations;

use App\Models\User;
use App\Models\Variation;
use App\Models\Workout;
use Database\Seeders\permissionsSeeders\VariationsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TranslateVariationsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'variations';
    const MODEL_SHOW_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';
    const MODEL_ES_NAME = 'Espalda baja';
    const MODEL_EN_NAME = 'Lower back';
    const MODEL_ES_PERFORMANCE = 'Rendimiento de la espalda baja';
    const MODEL_EN_PERFORMANCE = 'Lower back performance';

    protected User $user;
    protected Workout $workout;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(VariationsPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
        $this->workout = Workout::factory()->forCategory()->create();
    }

    /** @test */
    public function variations_can_have_name_translations()
    {
        $variation = Variation::factory(
            [
                'name' => self::MODEL_ES_NAME,
                'performance' => self::MODEL_ES_PERFORMANCE
            ]
        )->for($this->workout)
            ->hasTranslations(
                1,
                [
                    'locale'      => 'es',
                    'column'      => 'name',
                    'translation' => self::MODEL_ES_NAME,
                ]
            )
            ->create();

        // Make a request with spanish locale
        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withHeader('Locale', 'es')
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $variation));

        $response->assertFetchedOne(
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $variation->getRouteKey(),
                'attributes' => [
                    'name' => self::MODEL_ES_NAME,
                    'performance' => self::MODEL_ES_PERFORMANCE,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $variation)
                ]
            ]
        );

        // Check the app localization is set to 'es'
        $this->assertEquals('es', app()->getLocale());
    }

    /** @test */
    public function variations_can_have_performance_translations()
    {
        $variation = Variation::factory(
            [
                'name' => self::MODEL_EN_NAME,
                'performance' => self::MODEL_EN_PERFORMANCE
            ]
        )->for($this->workout)
            ->hasTranslations(
                1,
                [
                    'locale'      => 'en',
                    'column'      => 'performance',
                    'translation' => self::MODEL_EN_PERFORMANCE,
                ]
            )
            ->create();

        // Make a request with english locale
        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withHeader('Locale', 'en')
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $variation));

        $response->assertFetchedOne(
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $variation->getRouteKey(),
                'attributes' => [
                    'name' => self::MODEL_EN_NAME,
                    'performance' => self::MODEL_EN_PERFORMANCE,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $variation)
                ]
            ]
        );

        // Check the app localization is set to 'en'
        $this->assertEquals('en', app()->getLocale());
    }

    /** @test */
    public function variations_can_have_translations_for_its_attributes()
    {
        $variation = Variation::factory(
            [
                'name' => self::MODEL_ES_NAME,
                'performance' => self::MODEL_ES_PERFORMANCE
            ]
        )->for($this->workout)
            ->hasTranslations(
                1,
                [
                    'locale'      => 'es',
                    'column'      => 'name',
                    'translation' => self::MODEL_ES_NAME,
                ]
            )
            ->hasTranslations(
                1,
                [
                    'locale'      => 'es',
                    'column'      => 'performance',
                    'translation' => self::MODEL_ES_PERFORMANCE,
                ]
            )
            ->create();

        // Make a request with spanish locale
        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withHeader('Locale', 'es')
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $variation));

        $response->assertFetchedOne(
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $variation->getRouteKey(),
                'attributes' => [
                    'name' => self::MODEL_ES_NAME,
                    'performance' => self::MODEL_ES_PERFORMANCE,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $variation)
                ]
            ]
        );

        // Check the app localization is set to 'es'
        $this->assertEquals('es', app()->getLocale());
    }

    /** @test */
    public function translations_can_be_associated_to_variations()
    {
        $variation = Variation::factory()->for($this->workout)->create();

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
                        'type' => self::MODEL_PLURAL_NAME,
                        'id' => (string) $variation->getRouteKey(),
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
                'translationable_type' => Variation::class,
                'translationable_id'   => $variation->id,
            ]
        );
    }

    /** @test */
    public function variations_translations_can_be_updated()
    {
        $variation = Variation::factory()->for($this->workout)->create();

        $translation = $variation->translations()->create(
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
                'translation' => self::MODEL_ES_NAME . ' actualizado',
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects('translations')->withData($data)
            ->patch(route('v1.translations.update', $translation));

        $response->assertStatus(200);

        $this->assertDatabaseHas(
            'translations',
            [
                'locale'               => 'es',
                'column'               => 'name',
                'translation'          => self::MODEL_ES_NAME . ' actualizado',
                'translationable_type' => Variation::class,
                'translationable_id'   => $variation->id,
            ]
        );
    }
}
