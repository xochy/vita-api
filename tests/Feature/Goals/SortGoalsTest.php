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

class SortGoalsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'goals';
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
            $this->seed(GoalsPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function can_sort_goals_by_name_asc()
    {
        Goal::factory()->count(3)
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
            ->get($url)->assertSeeInOrder(
                [
                    self::MODEL_ALFA_NAME,
                    self::MODEL_BETA_NAME,
                    self::MODEL_GAMA_NAME,
                ]
            );
    }

    /** @test */
    public function can_sort_goals_by_name_desc()
    {
        Goal::factory()->count(3)
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
            ->get($url)->assertSeeInOrder(
                [
                    self::MODEL_GAMA_NAME,
                    self::MODEL_BETA_NAME,
                    self::MODEL_ALFA_NAME,
                ]
            );
    }

    /** @test */
    public function cannot_sort_goals_by_unknown_fields()
    {
        Goal::factory()->times(3)->create();

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
