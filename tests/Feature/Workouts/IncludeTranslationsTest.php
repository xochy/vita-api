<?php

namespace Tests\Feature\Workouts;

use App\Models\User;
use App\Models\Workout;
use Database\Seeders\permissionsSeeders\WorkoutsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IncludeTranslationsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'workouts';
    const MODEL_INCLUDE_RELATIONSHIP_NAME = 'translations';
    const MODEL_SHOW_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';
    const MODEL_SHOW_RELATIONSHIP_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.translations';
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

    const MODEL_RELATED_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME
        . '.' . self::MODEL_INCLUDE_RELATIONSHIP_NAME;

    const MODEL_SELF_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME
        . '.' . self::MODEL_INCLUDE_RELATIONSHIP_NAME . '.show';

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(WorkoutsPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function workouts_can_include_translations()
    {
        $workout = Workout::factory(
            [
                'name' => self::MODEL_EN_NAME,
                'performance' => self::MODEL_EN_PERFORMANCE,
                'comments' => self::MODEL_EN_COMMENTS,
                'corrections' => self::MODEL_EN_CORRECTIONS,
                'warnings' => self::MODEL_EN_WARNINGS,
            ]
        )->forCategory()
            ->hasTranslations(
                5,
                new Sequence(
                    [
                        'locale' => 'es',
                        'column' => 'name',
                        'translation' => self::MODEL_ES_NAME
                    ],
                    [
                        'locale' => 'es',
                        'column' => 'performance',
                        'translation' => self::MODEL_ES_PERFORMANCE
                    ],
                    [
                        'locale' => 'es',
                        'column' => 'comments',
                        'translation' => self::MODEL_ES_COMMENTS
                    ],
                    [
                        'locale' => 'es',
                        'column' => 'corrections',
                        'translation' => self::MODEL_ES_CORRECTIONS
                    ],
                    [
                        'locale' => 'es',
                        'column' => 'warnings',
                        'translation' => self::MODEL_ES_WARNINGS
                    ]
                )
            )->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_NAME)
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $workout));

        $response->assertSee($workout->translations[0]->slug);

        $response->assertJsonFragment(
            [
                'related' => route(self::MODEL_RELATED_ROUTE, $workout)
            ]
        );

        $response->assertJsonFragment(
            [
                'self' => route(self::MODEL_SELF_ROUTE, $workout)
            ]
        );
    }

    /** @test */
    public function workouts_can_fetch_related_translations()
    {
        $workout = Workout::factory(
            [
                'name' => self::MODEL_EN_NAME,
                'performance' => self::MODEL_EN_PERFORMANCE,
                'comments' => self::MODEL_EN_COMMENTS,
                'corrections' => self::MODEL_EN_CORRECTIONS,
                'warnings' => self::MODEL_EN_WARNINGS,
            ]
        )->forCategory()
            ->hasTranslations(
                5,
                new Sequence(
                    [
                        'locale' => 'es',
                        'column' => 'name',
                        'translation' => self::MODEL_ES_NAME
                    ],
                    [
                        'locale' => 'es',
                        'column' => 'performance',
                        'translation' => self::MODEL_ES_PERFORMANCE
                    ],
                    [
                        'locale' => 'es',
                        'column' => 'comments',
                        'translation' => self::MODEL_ES_COMMENTS
                    ],
                    [
                        'locale' => 'es',
                        'column' => 'corrections',
                        'translation' => self::MODEL_ES_CORRECTIONS
                    ],
                    [
                        'locale' => 'es',
                        'column' => 'warnings',
                        'translation' => self::MODEL_ES_WARNINGS
                    ]
                )
            )->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_INCLUDE_RELATIONSHIP_NAME)
            ->get(route(self::MODEL_SHOW_RELATIONSHIP_ROUTE, $workout));

        $response->assertJsonCount(5, 'data');

        $response->assertJsonPath('data.0.attributes.translation', self::MODEL_ES_NAME);
        $response->assertJsonPath('data.1.attributes.translation', self::MODEL_ES_PERFORMANCE);
        $response->assertJsonPath('data.2.attributes.translation', self::MODEL_ES_COMMENTS);
        $response->assertJsonPath('data.3.attributes.translation', self::MODEL_ES_CORRECTIONS);
        $response->assertJsonPath('data.4.attributes.translation', self::MODEL_ES_WARNINGS);
    }
}
