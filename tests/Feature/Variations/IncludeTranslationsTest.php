<?php

namespace Tests\Feature\Variations;

use App\Models\User;
use App\Models\Variation;
use App\Models\Workout;
use Database\Seeders\permissionsSeeders\VariationsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IncludeTranslationsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'variations';
    const MODEL_INCLUDE_RELATIONSHIP_NAME = 'translations';
    const MODEL_SHOW_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';
    const MODEL_SHOW_RELATIONSHIP_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.translations';
    const MODEL_ES_NAME = 'Espalda baja';
    const MODEL_EN_NAME = 'Lower back';
    const MODEL_ES_PERFORMANCE = 'Rendimiento de la espalda baja';
    const MODEL_EN_PERFORMANCE = 'Lower back performance';

    const MODEL_RELATED_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME
        . '.' . self::MODEL_INCLUDE_RELATIONSHIP_NAME;

    const MODEL_SELF_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME
        . '.' . self::MODEL_INCLUDE_RELATIONSHIP_NAME . '.show';

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
    public function variations_can_include_translations()
    {
        $variation = Variation::factory(
            [
                'name' => self::MODEL_ES_NAME,
                'performance' => self::MODEL_ES_PERFORMANCE
            ]
        )->for($this->workout)
            ->hasTranslations(
                2,
                new Sequence(
                    [
                        'locale' => 'es',
                        'column' => 'name',
                        'translation' => self::MODEL_ES_NAME,
                    ],
                    [
                        'locale' => 'es',
                        'column' => 'performance',
                        'translation' => self::MODEL_ES_PERFORMANCE,
                    ]

                )
            )->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_NAME)
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $variation));

        $response->assertSee($variation->translations[0]->slug);

        $response->assertJsonFragment(
            [
                'related' => route(self::MODEL_RELATED_ROUTE, $variation)
            ]
        );

        $response->assertJsonFragment(
            [
                'self' => route(self::MODEL_SELF_ROUTE, $variation)
            ]
        );
    }

    /** @test */
    public function variations_can_fetch_related_translations()
    {
        $variation = Variation::factory(
            [
                'name' => self::MODEL_ES_NAME,
                'performance' => self::MODEL_ES_PERFORMANCE
            ]
        )->for($this->workout)
            ->hasTranslations(
                2,
                new Sequence(
                    [
                        'locale' => 'es',
                        'column' => 'name',
                        'translation' => self::MODEL_ES_NAME,
                    ],
                    [
                        'locale' => 'es',
                        'column' => 'performance',
                        'translation' => self::MODEL_ES_PERFORMANCE,
                    ]

                )
            )->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects('translations')
            ->get(route(self::MODEL_SHOW_RELATIONSHIP_ROUTE, $variation));

        $response->assertJsonCount(2, 'data');

        $response->assertJsonPath('data.0.attributes.translation', self::MODEL_ES_NAME);
        $response->assertJsonPath('data.1.attributes.translation', self::MODEL_ES_PERFORMANCE);
    }
}
