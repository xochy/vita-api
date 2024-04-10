<?php

namespace Tests\Feature\Routines;

use App\Models\Routine;
use App\Models\User;
use Database\Seeders\permissionsSeeders\RoutinesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SortRoutinesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    const MODEL_PLURAL_NAME = 'routines';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.index';

    const MODEL_ALFA_NAME = 'alfa name';
    const MODEL_BETA_NAME = 'beta name';
    const MODEL_GAMA_NAME = 'gama name';

    const MODEL_SORT_PARAM_VALUE = 'name';

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(RoutinesPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('user');
    }

    /** @test */
    public function can_sort_routines_by_name_asc()
    {
        Routine::factory()->count(3)
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
    public function can_sort_routines_by_name_desc()
    {
        Routine::factory()->count(3)
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
    public function cannot_sort_routines_by_unknown_fields()
    {
        Routine::factory()->count(3)->create();

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
