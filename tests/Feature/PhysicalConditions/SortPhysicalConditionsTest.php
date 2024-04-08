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

class SortPhysicalConditionsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'physical-conditions';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.index';

    const MODEL_ALFA_NAME = 'alfa name';
    const MODEL_BETA_NAME = 'beta name';
    const MODEL_GAMA_NAME = 'gama name';

    const MODEL_SORT_PARAM_VALUE = 'name';

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
    public function can_sort_physical_conditions_by_name_asc()
    {
        PhysicalCondition::factory()->count(3)
            ->state(new Sequence(
                [self::MODEL_SORT_PARAM_VALUE => self::MODEL_GAMA_NAME],
                [self::MODEL_SORT_PARAM_VALUE => self::MODEL_ALFA_NAME],
                [self::MODEL_SORT_PARAM_VALUE => self::MODEL_BETA_NAME],
            ))
            ->create();

        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                'sort' => self::MODEL_SORT_PARAM_VALUE
            ]
        );

        $this->actingAs($this->user)->jsonApi()
            ->get($url)->assertSeeInOrder([
                self::MODEL_ALFA_NAME,
                self::MODEL_BETA_NAME,
                self::MODEL_GAMA_NAME,
            ]);
    }

    /** @test */
    public function can_sort_physical_conditions_by_name_desc()
    {
        PhysicalCondition::factory()->count(3)
            ->state(new Sequence(
                [self::MODEL_SORT_PARAM_VALUE => self::MODEL_GAMA_NAME],
                [self::MODEL_SORT_PARAM_VALUE => self::MODEL_ALFA_NAME],
                [self::MODEL_SORT_PARAM_VALUE => self::MODEL_BETA_NAME],
            ))
            ->create();

        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                'sort' => '-' . self::MODEL_SORT_PARAM_VALUE
            ]
        );

        $this->actingAs($this->user)->jsonApi()
            ->get($url)->assertSeeInOrder([
                self::MODEL_GAMA_NAME,
                self::MODEL_BETA_NAME,
                self::MODEL_ALFA_NAME,
            ]);
    }

    /** @test */
    public function cannot_sort_physical_conditions_by_unknown_fields()
    {
        PhysicalCondition::factory()->times(3)->create();

        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                'sort' => 'unknown_field'
            ]
        );

        $response = $this->actingAs($this->user)->jsonApi()->get($url);

        $response->assertError(
            400,
            [
                'source' => ['parameter' => 'sort'],
                'status' => '400',
                'title' => 'Invalid Query Parameter',
            ]
        );
    }
}
