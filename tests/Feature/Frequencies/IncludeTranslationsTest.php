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

class IncludeTranslationsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'frequencies';
    const MODEL_INCLUDE_RELATIONSHIP_NAME = 'translations';
    const MODEL_SHOW_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';
    const MODEL_SHOW_RELATIONSHIP_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.translations';
    const MODEL_ES_NAME = 'Dos veces por semana';
    const MODEL_EN_NAME = 'Twice a week';
    const MODEL_ES_DESCRIPTION = 'El plan consiste en realizar la actividad dos veces por semana';
    const MODEL_EN_DESCRIPTION = 'The plan consists of performing the activity twice a week';

    const MODEL_RELATED_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME
        . '.' . self::MODEL_INCLUDE_RELATIONSHIP_NAME;

    const MODEL_SELF_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME
        . '.' . self::MODEL_INCLUDE_RELATIONSHIP_NAME . '.show';

    protected User $user;
    protected string $token;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(FrequenciesPermissionsSeeder::class);
        }

        [$this->user, $this->token] = $this->createUserWithToken('admin');
    }

    /** @test */
    public function frequencies_can_include_translations()
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
            ->expects(self::MODEL_PLURAL_NAME)
            ->withHeader('Authorization', $this->token)
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $frequency));

        $response->assertSee($frequency->translations[0]->slug);

        $response->assertJsonFragment(
            [
                'related' => route(self::MODEL_RELATED_ROUTE, $frequency)
            ]
        );

        $response->assertJsonFragment(
            [
                'self' => route(self::MODEL_SELF_ROUTE, $frequency)
            ]
        );
    }

    /** @test */
    public function frequencies_can_fetch_related_translations()
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
            ->withHeader('Authorization', $this->token)
            ->get(route(self::MODEL_SHOW_RELATIONSHIP_ROUTE, $frequency));

        $response->assertJsonCount(2, 'data');

        $response->assertJsonPath('data.0.attributes.translation', self::MODEL_ES_NAME);
        $response->assertJsonPath('data.1.attributes.translation', self::MODEL_ES_DESCRIPTION);
    }
}
