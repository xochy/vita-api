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

class FilterRoutinesTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_SINGLE_NAME = 'routine';
    const MODEL_PLURAL_NAME = 'routines';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.index';

    const MODEL_BETA_NAME = 'beta name';
    const MODEL_ALFA_NAME = 'alfa name';
    const MODEL_GAMA_NAME = 'gama name';

    const MODEL_GAMA_DESCRIPTION = 'gama description';
    const MODEL_BETA_DESCRIPTION = 'beta description';
    const MODEL_ALFA_DESCRIPTION = 'alfa description';

    const MODEL_PI_NAME = 'pi lambda name';
    const MODEL_JI_NAME = 'ji lambda name';

    const MODEL_EXTRA_SEARCHING_TERM = 'omega';
    const MODEL_MULTIPLE_SEARCH_TERM = self::MODEL_SINGLE_NAME . ' ' . 'lambda';

    const MODEL_FILTER_NAME_PARAM_NAME = 'filter[name]';
    const MODEL_FILTER_SEARCH_PARAM_NAME = 'filter[search]';
    const MODEL_FILTER_UNKNOWN_PARAM_NAME = 'filter[unknown]';

    protected User $user;

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
    public function can_filter_rotines_by_name()
    {
        Routine::factory()->count(3)
            ->state(new Sequence(
                ['name' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_NAME],
                ['name' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_NAME],
                ['name' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_NAME],
            ))
            ->create();

        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                self::MODEL_FILTER_NAME_PARAM_NAME => 'alfa'
            ]
        );

        $this->actingAs($this->user)->jsonApi()->get($url)
            ->assertJsonCount(1, 'data')
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_NAME)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_NAME)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_NAME);
    }

    /** @test */
    public function cannot_filter_routines_by_unknown_filters()
    {
        Routine::factory()->create();

        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                self::MODEL_FILTER_UNKNOWN_PARAM_NAME => 2
            ]
        );

        $response = $this->actingAs($this->user)->jsonApi()->get($url);

        // Bad Request
        $response->assertStatus(400);
    }

    /** @test */
    public function can_search_routines_by_name()
    {
        Routine::factory()->count(3)
            ->state(new Sequence(
                ['name' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_NAME],
                ['name' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_NAME],
                ['name' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_NAME],
            ))
            ->create();

        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                self::MODEL_FILTER_SEARCH_PARAM_NAME => 'alfa'
            ]
        );

        $this->actingAs($this->user)->jsonApi()->get($url)
            ->assertJsonCount(1, 'data')
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_NAME)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_NAME)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_NAME);
    }
}
