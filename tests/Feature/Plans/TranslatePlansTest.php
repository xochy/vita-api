<?php

namespace Tests\Feature\Plans;

use App\Models\Plan;
use App\Models\User;
use Database\Seeders\permissionsSeeders\PlansPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TranslatePlansTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'plans';
    const MODEL_SHOW_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';
    const MODEL_ES_NAME = 'Plan para bajar de peso';
    const MODEL_EN_NAME = 'Weight loss plan';

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(PlansPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function plans_can_have_name_translations()
    {
        $plan = Plan::factory(
            [
                'name' => self::MODEL_EN_NAME,
            ]
        )->forGoal()->forFrequency()->forPhysicalCondition()
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
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $plan));

        $response->assertFetchedOne(
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $plan->getRouteKey(),
                'attributes' => [
                    'name' => self::MODEL_ES_NAME,
                    'slug' => $plan->slug,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $plan)
                ]
            ]
        );
    }

    /** @test */
    public function translations_can_be_associated_to_plans()
    {
        $plan =  Plan::factory()
            ->forGoal()->forFrequency()->forPhysicalCondition()->create();

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
                        'type' => 'plans',
                        'id' => (string) $plan->getRouteKey(),
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
                'translationable_type' => Plan::class,
                'translationable_id'   => $plan->id,
            ]
        );
    }

    /** @test */
    public function plans_translations_can_be_updated()
    {
        $plan = Plan::factory()
            ->forGoal()->forFrequency()->forPhysicalCondition()->create();

        $translation = $plan->translations()->create(
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
                'translation' => 'Plan para subir de peso actualizado',
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
                'translation'          => 'Plan para subir de peso actualizado',
                'translationable_type' => Plan::class,
                'translationable_id'   => $plan->id,
            ]
        );
    }
}
