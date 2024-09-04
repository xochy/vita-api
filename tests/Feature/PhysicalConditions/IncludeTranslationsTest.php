<?php

namespace Tests\Feature\PhysicalConditions;

use App\Models\PhysicalCondition;
use App\Models\User;
use Database\Seeders\permissionsSeeders\PhysicalConditionsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IncludeTranslationsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'physical-conditions';
    const MODEL_INCLUDE_RELATIONSHIP_NAME = 'translations';
    const MODEL_SHOW_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';
    const MODEL_SHOW_RELATIONSHIP_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.translations';
    const MODEL_ES_NAME = 'Obesidad mórbida';
    const MODEL_EN_NAME = 'Morbid obesity';
    const MODEL_ES_DESCRIPTION = 'El usuario tiene obesidad mórbida cuando su IMC es mayor a 40';
    const MODEL_EN_DESCRIPTION = 'The user has morbid obesity when their BMI is greater than 40';

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
            $this->seed(PhysicalConditionsPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function physical_conditions_can_include_translations()
    {
        $physicalCondition = PhysicalCondition::factory(
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

        $response = $this->actingAs($this->user)->jsonApi()
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_NAME)
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $physicalCondition));

        $response->assertSee($physicalCondition->translations[0]->slug);

        $response->assertJsonFragment(
            [
                'related' => route(self::MODEL_RELATED_ROUTE, $physicalCondition)
            ]
        );

        $response->assertJsonFragment(
            [
                'self' => route(self::MODEL_SELF_ROUTE, $physicalCondition)
            ]
        );
    }

    /** @test */
    public function physical_conditions_can_fetch_related_translations()
    {
        $physicalCondition = PhysicalCondition::factory(
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

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects('translations')
            ->get(route(self::MODEL_SHOW_RELATIONSHIP_ROUTE, $physicalCondition));

        $response->assertJsonCount(2, 'data');

        $response->assertJsonPath('data.0.attributes.translation', self::MODEL_ES_NAME);
        $response->assertJsonPath('data.1.attributes.translation', self::MODEL_ES_DESCRIPTION);
    }
}
