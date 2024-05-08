<?php

namespace Tests\Feature\Goals;

use App\Models\Goal;
use App\Models\User;
use Database\Seeders\permissionsSeeders\GoalsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TranslateGoalsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'goals';
    const MODEL_SHOW_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';
    const MODEL_ES_NAME = 'Peso saludable';
    const MODEL_EN_NAME = 'Healthy weight';
    const MODEL_ES_DESCRIPTION = 'Se encarga de mantener un peso saludable';
    const MODEL_EN_DESCRIPTION = 'It is in charge of maintaining a healthy weight';

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(GoalsPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function goals_can_have_name_translations()
    {
        $goal = Goal::factory(
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
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $goal));

        $response->assertFetchedOne(
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $goal->getRouteKey(),
                'attributes' => [
                    'name'        => self::MODEL_ES_NAME,
                    'description' => $goal->description,
                    'slug'        => $goal->slug,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $goal)
                ]
            ]
        );

        // Check the app localization is set to 'es'
        $this->assertEquals('es', app()->getLocale());
    }

    /** @test */
    public function goals_can_have_description_translations()
    {
        $goal = Goal::factory(
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
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $goal));

        $response->assertFetchedOne(
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $goal->getRouteKey(),
                'attributes' => [
                    'name'        => $goal->name,
                    'description' => self::MODEL_ES_DESCRIPTION,
                    'slug'        => $goal->slug,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $goal)
                ]
            ]
        );

        // Check the app localization is set to 'es'
        $this->assertEquals('es', app()->getLocale());
    }

    /** @test */
    public function goals_can_have_translations_for_its_attributes()
    {
        $goal = Goal::factory(
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
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $goal));

        $response->assertFetchedOne(
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $goal->getRouteKey(),
                'attributes' => [
                    'name'        => self::MODEL_ES_NAME,
                    'description' => self::MODEL_ES_DESCRIPTION,
                    'slug'        => $goal->slug,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $goal)
                ]
            ]
        );

        // Check the app localization is set to 'es'
        $this->assertEquals('es', app()->getLocale());
    }

    /** @test */
    public function translations_can_be_associated_to_goals()
    {
        $goal = Goal::factory()->create();

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
                        'type' => 'goals',
                        'id' => (string) $goal->getRouteKey(),
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
            'translationable_type' => Goal::class,
            'translationable_id'   => $goal->id,
        ]);
    }

    /** @test */
    public function goals_translations_can_be_updated()
    {
        $goal = Goal::factory()->create();

        $translation = $goal->translations()->create(
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
                'translation' => 'Peso saludable actualizado',
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
                'translation'           => 'Peso saludable actualizado',
                'translationable_type'  => Goal::class,
                'translationable_id'    => $goal->id,
            ]
        );
    }
}
