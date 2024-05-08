<?php

namespace Tests\Feature\Frequencies;

use App\Models\Frequency;
use App\Models\User;
use Database\Seeders\permissionsSeeders\FrequenciesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TranslateFrequenciesTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'frequencies';
    const MODEL_SHOW_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';
    const MODEL_ES_NAME = 'Dos veces por semana';
    const MODEL_EN_NAME = 'Twice a week';
    const MODEL_ES_DESCRIPTION = 'El plan consiste en realizar la actividad dos veces por semana';
    const MODEL_EN_DESCRIPTION = 'The plan consists of performing the activity twice a week';

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(FrequenciesPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function frequencies_can_have_name_translations()
    {
        $frequency = Frequency::factory(
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
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $frequency));

        $response->assertFetchedOne(
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $frequency->getRouteKey(),
                'attributes' => [
                    'name'        => self::MODEL_ES_NAME,
                    'description' => $frequency->description,
                    'slug'        => $frequency->slug,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $frequency)
                ]
            ]
        );

        // Check the app localization is set to 'es'
        $this->assertEquals('es', app()->getLocale());
    }

    /** @test */
    public function frequencies_can_have_description_translations()
    {
        $frequency = Frequency::factory(
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
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $frequency));

        $response->assertFetchedOne(
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $frequency->getRouteKey(),
                'attributes' => [
                    'name'        => $frequency->name,
                    'description' => self::MODEL_ES_DESCRIPTION,
                    'slug'        => $frequency->slug,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $frequency)
                ]
            ]
        );

        // Check the app localization is set to 'es'
        $this->assertEquals('es', app()->getLocale());
    }

    /** @test */
    public function frequencies_can_have_translations_for_its_attributes()
    {
        $frequency = Frequency::factory(
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
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $frequency));

        $response->assertFetchedOne(
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $frequency->getRouteKey(),
                'attributes' => [
                    'name'        => self::MODEL_ES_NAME,
                    'description' => self::MODEL_ES_DESCRIPTION,
                    'slug'        => $frequency->slug,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $frequency)
                ]
            ]
        );

        // Check the app localization is set to 'es'
        $this->assertEquals('es', app()->getLocale());
    }

    /** @test */
    public function translations_can_be_associated_to_frequencies()
    {
        $frequency = Frequency::factory()->create();

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
                        'type' => 'frequencies',
                        'id' => (string) $frequency->getRouteKey(),
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
                'translationable_type' => Frequency::class,
                'translationable_id'   => $frequency->id,
            ]
        );
    }

    /** @test */
    public function frequencies_translations_can_be_updated()
    {
        $frequency = Frequency::factory()->create();

        $translation = $frequency->translations()->create(
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
                'translation' => 'Dos veces por semana actualizado',
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects('translations')->withData($data)
            ->patch(route('v1.translations.update', $translation));

        $response->assertStatus(200);

        $this->assertDatabaseHas(
            'translations',
            [
                'id'                    => $translation->id,
                'locale'                => 'es',
                'column'                => 'name',
                'translation'           => 'Dos veces por semana actualizado',
                'translationable_type'  => Frequency::class,
                'translationable_id'    => $frequency->id,
            ]
        );
    }
}
