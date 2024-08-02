<?php

namespace Tests\Feature\Workouts;

use App\Models\Category;
use App\Models\User;
use App\Models\Workout;
use Database\Seeders\permissionsSeeders\WorkoutsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TranslateWorkoutsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'workouts';
    const MODEL_SHOW_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';
    const MODEL_ES_NAME = 'Espalda baja';
    const MODEL_EN_NAME = 'Lower back';
    const MODEL_ES_PERFORMANCE = 'Rendimiento de la espalda baja';
    const MODEL_EN_PERFORMANCE = 'Lower back performance';
    const MODEL_ES_COMMENTS = 'Comentarios de la espalda baja';
    const MODEL_EN_COMMENTS = 'Lower back comments';
    const MODEL_ES_CORRECTIONS = 'Correcciones de la espalda baja';
    const MODEL_EN_CORRECTIONS = 'Lower back corrections';
    const MODEL_ES_WARNINGS = 'Advertencias de la espalda baja';
    const MODEL_EN_WARNINGS = 'Lower back warnings';

    protected User $user;
    protected Category $category;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(WorkoutsPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
        $this->category = Category::factory()->create();
    }

    /** @test */
    public function workouts_can_have_name_translations()
    {
        $workout = Workout::factory(
            [
                'name'        => self::MODEL_EN_NAME,
                'performance' => self::MODEL_EN_PERFORMANCE,
                'comments'    => self::MODEL_EN_COMMENTS,
                'corrections' => self::MODEL_EN_CORRECTIONS,
                'warnings'    => self::MODEL_EN_WARNINGS,
            ]
        )->for($this->category)
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
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $workout));

        $response->assertFetchedOne(
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $workout->getRouteKey(),
                'attributes' => [
                    'name'        => self::MODEL_ES_NAME,
                    'performance' => $workout->performance,
                    'comments'    => $workout->comments,
                    'corrections' => $workout->corrections,
                    'warnings'    => $workout->warnings,
                    'slug'        => $workout->slug,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $workout)
                ]
            ]
        );

        // Check the app localization is set to 'es'
        $this->assertEquals('es', app()->getLocale());
    }

    /** @test */
    public function workouts_can_have_performance_translations()
    {
        $workout = Workout::factory(
            [
                'name'        => self::MODEL_EN_NAME,
                'performance' => self::MODEL_EN_PERFORMANCE,
                'comments'    => self::MODEL_EN_COMMENTS,
                'corrections' => self::MODEL_EN_CORRECTIONS,
                'warnings'    => self::MODEL_EN_WARNINGS,
            ]
        )->for($this->category)
            ->hasTranslations(
                1,
                [
                    'locale'      => 'es',
                    'column'      => 'performance',
                    'translation' => self::MODEL_ES_PERFORMANCE,
                ]
            )->create();

        // Make a request with spanish locale
        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withHeader('Locale', 'es')
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $workout));

        $response->assertFetchedOne(
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $workout->getRouteKey(),
                'attributes' => [
                    'name'        => $workout->name,
                    'performance' => self::MODEL_ES_PERFORMANCE,
                    'comments'    => $workout->comments,
                    'corrections' => $workout->corrections,
                    'warnings'    => $workout->warnings,
                    'slug'        => $workout->slug,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $workout)
                ]
            ]
        );

        // Check the app localization is set to 'es'
        $this->assertEquals('es', app()->getLocale());
    }

    /** @test */
    public function workouts_can_have_comments_translations()
    {
        $workout = Workout::factory(
            [
                'name'        => self::MODEL_EN_NAME,
                'performance' => self::MODEL_EN_PERFORMANCE,
                'comments'    => self::MODEL_EN_COMMENTS,
                'corrections' => self::MODEL_EN_CORRECTIONS,
                'warnings'    => self::MODEL_EN_WARNINGS,
            ]
        )->for($this->category)
            ->hasTranslations(
                1,
                [
                    'locale'      => 'es',
                    'column'      => 'comments',
                    'translation' => self::MODEL_ES_COMMENTS,
                ]
            )->create();

        // Make a request with spanish locale
        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withHeader('Locale', 'es')
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $workout));

        $response->assertFetchedOne(
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $workout->getRouteKey(),
                'attributes' => [
                    'name'        => $workout->name,
                    'performance' => $workout->performance,
                    'comments'    => self::MODEL_ES_COMMENTS,
                    'corrections' => $workout->corrections,
                    'warnings'    => $workout->warnings,
                    'slug'        => $workout->slug,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $workout)
                ]
            ]
        );

        // Check the app localization is set to 'es'
        $this->assertEquals('es', app()->getLocale());
    }

    /** @test */
    public function workouts_can_have_corrections_translations()
    {
        $workout = Workout::factory(
            [
                'name'        => self::MODEL_EN_NAME,
                'performance' => self::MODEL_EN_PERFORMANCE,
                'comments'    => self::MODEL_EN_COMMENTS,
                'corrections' => self::MODEL_EN_CORRECTIONS,
                'warnings'    => self::MODEL_EN_WARNINGS,
            ]
        )->for($this->category)
            ->hasTranslations(
                1,
                [
                    'locale'      => 'es',
                    'column'      => 'corrections',
                    'translation' => self::MODEL_ES_CORRECTIONS,
                ]
            )->create();

        // Make a request with spanish locale
        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withHeader('Locale', 'es')
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $workout));

        $response->assertFetchedOne(
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $workout->getRouteKey(),
                'attributes' => [
                    'name'        => $workout->name,
                    'performance' => $workout->performance,
                    'comments'    => $workout->comments,
                    'corrections' => self::MODEL_ES_CORRECTIONS,
                    'warnings'    => $workout->warnings,
                    'slug'        => $workout->slug,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $workout)
                ]
            ]
        );

        // Check the app localization is set to 'es'
        $this->assertEquals('es', app()->getLocale());
    }

    /** @test */
    public function workouts_can_have_warnings_translations()
    {
        $workout = Workout::factory(
            [
                'name'        => self::MODEL_EN_NAME,
                'performance' => self::MODEL_EN_PERFORMANCE,
                'comments'    => self::MODEL_EN_COMMENTS,
                'corrections' => self::MODEL_EN_CORRECTIONS,
                'warnings'    => self::MODEL_EN_WARNINGS,
            ]
        )->for($this->category)
            ->hasTranslations(
                1,
                [
                    'locale'      => 'es',
                    'column'      => 'warnings',
                    'translation' => self::MODEL_ES_WARNINGS,
                ]
            )->create();

        // Make a request with spanish locale
        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withHeader('Locale', 'es')
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $workout));

        $response->assertFetchedOne(
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $workout->getRouteKey(),
                'attributes' => [
                    'name'        => $workout->name,
                    'performance' => $workout->performance,
                    'comments'    => $workout->comments,
                    'corrections' => $workout->corrections,
                    'warnings'    => self::MODEL_ES_WARNINGS,
                    'slug'        => $workout->slug,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $workout)
                ]
            ]
        );

        // Check the app localization is set to 'es'
        $this->assertEquals('es', app()->getLocale());
    }

    /** @test */
    public function workouts_can_have_translations_for_its_attributes()
    {
        $workout = Workout::factory(
            [
                'name'        => self::MODEL_EN_NAME,
                'performance' => self::MODEL_EN_PERFORMANCE,
                'comments'    => self::MODEL_EN_COMMENTS,
                'corrections' => self::MODEL_EN_CORRECTIONS,
                'warnings'    => self::MODEL_EN_WARNINGS,
            ]
        )->for($this->category)
            ->hasTranslations(
                5,
                new Sequence(
                    [
                        'locale'      => 'es',
                        'column'      => 'name',
                        'translation' => self::MODEL_ES_NAME
                    ],
                    [
                        'locale'      => 'es',
                        'column'      => 'performance',
                        'translation' => self::MODEL_ES_PERFORMANCE
                    ],
                    [
                        'locale'      => 'es',
                        'column'      => 'comments',
                        'translation' => self::MODEL_ES_COMMENTS
                    ],
                    [
                        'locale'      => 'es',
                        'column'      => 'corrections',
                        'translation' => self::MODEL_ES_CORRECTIONS
                    ],
                    [
                        'locale'      => 'es',
                        'column'      => 'warnings',
                        'translation' => self::MODEL_ES_WARNINGS
                    ]
                )
            )->create();

        // Make a request with spanish locale
        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withHeader('Locale', 'es')
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $workout));

        $response->assertFetchedOne(
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $workout->getRouteKey(),
                'attributes' => [
                    'name'        => self::MODEL_ES_NAME,
                    'performance' => self::MODEL_ES_PERFORMANCE,
                    'comments'    => self::MODEL_ES_COMMENTS,
                    'corrections' => self::MODEL_ES_CORRECTIONS,
                    'warnings'    => self::MODEL_ES_WARNINGS,
                    'slug'        => $workout->slug,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $workout)
                ]
            ]
        );

        // Check the app localization is set to 'es'
        $this->assertEquals('es', app()->getLocale());
    }

    /** @test */
    public function translations_can_be_associated_to_workouts()
    {
        $workout = Workout::factory()->for($this->category)->create();

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
                        'id' => (string) $workout->getRouteKey(),
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
                'translationable_type' => Workout::class,
                'translationable_id'   => $workout->id,
            ]
        );
    }

    /** @test */
    public function workouts_translations_can_be_updated()
    {
        $workout = Workout::factory()->for($this->category)->create();

        $translation = $workout->translations()->create(
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
                'translationable_type' => Workout::class,
                'translationable_id'   => $workout->id,
            ]
        );
    }
}
