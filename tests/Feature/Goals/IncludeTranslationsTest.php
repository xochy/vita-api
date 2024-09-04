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

class IncludeTranslationsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'goals';
    const MODEL_INCLUDE_RELATIONSHIP_NAME = 'translations';
    const MODEL_SHOW_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';
    const MODEL_SHOW_RELATIONSHIP_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.translations';
    const MODEL_ES_NAME = 'Peso saludable';
    const MODEL_EN_NAME = 'Healthy weight';
    const MODEL_ES_DESCRIPTION = 'Se encarga de mantener un peso saludable';
    const MODEL_EN_DESCRIPTION = 'It is in charge of maintaining a healthy weight';

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
            $this->seed(GoalsPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function goals_can_include_translations()
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
                        'locale' => 'es',
                        'column' => 'name',
                        'translation' => self::MODEL_ES_NAME,
                    ],
                    [
                        'locale' => 'es',
                        'column' => 'description',
                        'translation' => self::MODEL_ES_DESCRIPTION,
                    ]
                )
            )->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_NAME)
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $goal));

        $response->assertSee($goal->translations[0]->slug);

        $response->assertJsonFragment(
            [
                'related' => route(self::MODEL_RELATED_ROUTE, $goal)
            ]
        );

        $response->assertJsonFragment(
            [
                'self' => route(self::MODEL_SELF_ROUTE, $goal)
            ]
        );
    }

    /** @test */
    public function goals_can_fetch_related_translations()
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
                        'locale' => 'es',
                        'column' => 'name',
                        'translation' => self::MODEL_ES_NAME,
                    ],
                    [
                        'locale' => 'es',
                        'column' => 'description',
                        'translation' => self::MODEL_ES_DESCRIPTION,
                    ]
                )
            )->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects('translations')
            ->get(route(self::MODEL_SHOW_RELATIONSHIP_ROUTE, $goal));

        $response->assertJsonCount(2, 'data');

        $response->assertJsonPath('data.0.attributes.translation', self::MODEL_ES_NAME);
        $response->assertJsonPath('data.1.attributes.translation', self::MODEL_ES_DESCRIPTION);
    }
}
