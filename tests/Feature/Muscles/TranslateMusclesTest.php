<?php

namespace Tests\Feature\Muscles;

use App\Models\Muscle;
use App\Models\Translation;
use App\Models\User;
use Database\Seeders\permissionsSeeders\MusclesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TranslateMusclesTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'muscles';
    const MODEL_SHOW_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';
    const MODEL_ES_NAME = 'Espalda baja';
    const MODEL_EN_NAME = 'Lower back';
    const MODEL_ES_DESCRIPTION = 'Descripción del músculo en español';
    const MODEL_EN_DESCRIPTION = 'Muscle description in english';

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(MusclesPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function muscles_can_have_name_translations()
    {
        $muscle = Muscle::factory(
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
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $muscle));

        $response->assertFetchedOne(
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $muscle->getRouteKey(),
                'attributes' => [
                    'name'        => self::MODEL_ES_NAME,
                    'description' => $muscle->description,
                    'slug'        => $muscle->slug,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $muscle)
                ]
            ]
        );

        // Check the app localization is set to 'es'
        $this->assertEquals('es', app()->getLocale());
    }

    /** @test */
    public function muscles_can_have_description_translations()
    {
        $muscle = Muscle::factory(
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
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $muscle));

        $response->assertFetchedOne(
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $muscle->getRouteKey(),
                'attributes' => [
                    'name'        => $muscle->name,
                    'description' => self::MODEL_ES_DESCRIPTION,
                    'slug'        => $muscle->slug,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $muscle)
                ]
            ]
        );

        // Check the app localization is set to 'es'
        $this->assertEquals('es', app()->getLocale());
    }

    /** @test */
    public function muscles_can_have_translations_for_its_attributes()
    {
        $muscle = Muscle::factory(
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
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $muscle));

        $response->assertFetchedOne(
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $muscle->getRouteKey(),
                'attributes' => [
                    'name'        => self::MODEL_ES_NAME,
                    'description' => self::MODEL_ES_DESCRIPTION,
                    'slug'        => $muscle->slug,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $muscle)
                ]
            ]
        );

        // Check the app localization is set to 'es'
        $this->assertEquals('es', app()->getLocale());
    }

    /** @test */
    public function translations_can_be_associated_to_muscles()
    {
        $muscle = Muscle::factory()->create();

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
                        'id' => (string) $muscle->getRouteKey(),
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects('translations')->withData($data)
            ->post(route('v1.translations.store'));

        $response->assertCreated();

        $this->assertDatabaseHas('translations',
        [
            'locale'               => 'es',
            'column'               => 'name',
            'translation'          => self::MODEL_ES_NAME,
            'translationable_id'   => $muscle->id,
            'translationable_type' => Muscle::class,
        ]);
    }

    /** @test */
    public function muscles_translations_can_be_updated()
    {
        $muscle = Muscle::factory()->create();

        $transaltion = $muscle->translations()->create(
            [
                'locale' => 'es',
                'column' => 'name',
                'translation' => self::MODEL_ES_NAME,
            ]
        );

        $data = [
            'type' => 'translations',
            'id' => (string) $transaltion->getRouteKey(),
            'attributes' => [
                'translation' => 'Espalda baja actualizado',
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects('translations')->withData($data)
            ->patch(route('v1.translations.update', $transaltion));

        $response->assertStatus(200);

        $this->assertDatabaseHas(
            'translations',
            [
                'id'                   => $transaltion->id,
                'locale'               => 'es',
                'column'               => 'name',
                'translation'          => 'Espalda baja actualizado',
                'translationable_id'   => $muscle->id,
                'translationable_type' => Muscle::class,
            ]
        );
    }
}
