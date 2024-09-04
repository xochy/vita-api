<?php

namespace Tests\Feature\Muscles;

use App\Models\Muscle;
use App\Models\User;
use Database\Seeders\permissionsSeeders\MusclesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IncludeTranslationsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'muscles';
    const MODEL_INCLUDE_RELATIONSHIP_NAME = 'translations';
    const MODEL_SHOW_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';
    const MODEL_SHOW_RELATIONSHIP_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.translations';
    const MODEL_ES_NAME = 'Espalda baja';
    const MODEL_EN_NAME = 'Lower back';
    const MODEL_ES_DESCRIPTION = 'Descripción del músculo en español';
    const MODEL_EN_DESCRIPTION = 'Muscle description in english';

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
            $this->seed(MusclesPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function muscles_can_include_translations()
    {
        $muscle = Muscle::factory(
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
            ->expects(self::MODEL_PLURAL_NAME)
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $muscle));

        $response->assertSee($muscle->translations[0]->slug);

        $response->assertJsonFragment(
            [
                'related' => route(self::MODEL_RELATED_ROUTE, $muscle)
            ]
        );

        $response->assertJsonFragment(
            [
                'self' => route(self::MODEL_SELF_ROUTE, $muscle)
            ]
        );
    }

    /** @test */
    public function muscles_can_fetch_related_translations()
    {
        $muscle = Muscle::factory(
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
            ->get(route(self::MODEL_SHOW_RELATIONSHIP_ROUTE, $muscle));

        $response->assertJsonCount(2, 'data');

        $response->assertJsonPath('data.0.attributes.translation', self::MODEL_ES_NAME);
        $response->assertJsonPath('data.1.attributes.translation', self::MODEL_ES_DESCRIPTION);
    }
}
