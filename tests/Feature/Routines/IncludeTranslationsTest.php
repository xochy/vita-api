<?php

namespace Tests\Feature\Routines;

use App\Models\Routine;
use App\Models\User;
use Database\Seeders\permissionsSeeders\RoutinesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IncludeTranslationsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'routines';
    const MODEL_INCLUDE_RELATIONSHIP_NAME = 'translations';
    const MODEL_SHOW_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';
    const MODEL_SHOW_RELATIONSHIP_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.translations';
    const MODEL_ES_NAME = 'Rutina de espalda';
    const MODEL_EN_NAME = 'Back routine';

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
            $this->seed(RoutinesPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function routines_can_include_translations()
    {
        $routine = Routine::factory(
            [
                'name' => self::MODEL_EN_NAME,
            ]
        )->hasTranslations(
            1,
            [
                'locale'      => 'es',
                'column'      => 'name',
                'translation' => self::MODEL_ES_NAME,
            ]
        )->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_NAME)
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $routine));

        $response->assertSee($routine->translations[0]->slug);

        $response->assertJsonFragment(
            [
                'related' => route(self::MODEL_RELATED_ROUTE, $routine)
            ]
        );

        $response->assertJsonFragment(
            [
                'self' => route(self::MODEL_SELF_ROUTE, $routine)
            ]
        );
    }

    /** @test */
    public function routines_can_fetch_related_translations()
    {
        $routine = Routine::factory(
            [
                'name' => self::MODEL_EN_NAME,
            ]
        )->hasTranslations(
            1,
            [
                'locale'      => 'es',
                'column'      => 'name',
                'translation' => self::MODEL_ES_NAME,
            ]
        )->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects('translations')
            ->get(route(self::MODEL_SHOW_RELATIONSHIP_ROUTE, $routine));

        $response->assertJsonCount(1, 'data');

        $response->assertJsonPath('data.0.attributes.translation', self::MODEL_ES_NAME);
    }
}
