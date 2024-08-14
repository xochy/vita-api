<?php

namespace Tests\Feature\Variations;

use App\Models\User;
use App\Models\Variation;
use App\Models\Workout;
use Database\Seeders\permissionsSeeders\VariationsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FilterVariationsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_SINGLE_NAME = 'variation';
    const MODEL_PLURAL_NAME = 'variations';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.index';

    const MODEL_BETA_NAME = 'beta name';
    const MODEL_ALFA_NAME = 'alfa name';
    const MODEL_GAMA_NAME = 'gama name';

    const MODEL_GAMA_PERFORMANCE = 'gama performance';
    const MODEL_BETA_PERFORMANCE = 'beta performance';
    const MODEL_ALFA_PERFORMANCE = 'alfa performance';

    const MODEL_PI_NAME = 'pi lambda name';
    const MODEL_JI_NAME = 'ji lambda name';

    const MODEL_EXTRA_SEARCHING_TERM = 'omega';
    const MODEL_MULTIPLE_SEARCH_TERM = self::MODEL_SINGLE_NAME . ' ' . 'lambda';

    const MODEL_FILTER_NAME_PARAM_NAME = 'filter[name]';
    const MODEL_FILTER_SEARCH_PARAM_NAME = 'filter[search]';
    const MODEL_FILTER_UNKNOWN_PARAM_NAME = 'filter[unknown]';
    const MODEL_FILTER_PERFORMANCE_PARAM_NAME = 'filter[performance]';

    protected User $user;
    protected Workout $workout;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(VariationsPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
        $this->workout = Workout::factory()->forCategory()->create();
    }

    /** @test */
    public function can_filter_variations_by_name()
    {
        Variation::factory()->for($this->workout)->count(3)
            ->state(
                new Sequence(
                    ['name' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_NAME],
                    ['name' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_NAME],
                    ['name' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_NAME],
                )
            )
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
    public function can_filter_variations_by_performance()
    {
        Variation::factory()->for($this->workout)->count(3)
            ->state(
                new Sequence(
                    ['performance' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_PERFORMANCE],
                    ['performance' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_PERFORMANCE],
                    ['performance' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_PERFORMANCE],
                )
            )
            ->create();

        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                self::MODEL_FILTER_PERFORMANCE_PARAM_NAME => 'alfa'
            ]
        );

        $this->actingAs($this->user)->jsonApi()->get($url)
            ->assertJsonCount(1, 'data')
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_PERFORMANCE)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_PERFORMANCE)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_PERFORMANCE);
    }

    /** @test */
    public function can_filter_variations_by_name_and_performance()
    {
        Variation::factory()->for($this->workout)->count(3)
            ->state(
                new Sequence(
                    [
                        'name' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_NAME,
                        'performance' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_PERFORMANCE
                    ],
                    [
                        'name' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_NAME,
                        'performance' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_PERFORMANCE
                    ],
                    [
                        'name' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_NAME,
                        'performance' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_PERFORMANCE
                    ],
                )
            )
            ->create();

        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                self::MODEL_FILTER_NAME_PARAM_NAME => 'alfa',
                self::MODEL_FILTER_PERFORMANCE_PARAM_NAME => 'alfa'
            ]
        );

        $this->actingAs($this->user)->jsonApi()->get($url)
            ->assertJsonCount(1, 'data')
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_NAME)
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_PERFORMANCE)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_NAME)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_PERFORMANCE)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_NAME)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_PERFORMANCE);
    }

    /** @test */
    public function cannot_filter_variations_by_unknown_filters()
    {
        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                self::MODEL_FILTER_UNKNOWN_PARAM_NAME => 2
            ]
        );

        $response = $this->actingAs($this->user)->jsonApi()
            ->get($url);

        // Bad Request
        $response->assertError(
            400,
            [
                'title' => 'Invalid Query Parameter',
                'detail' => 'Filter parameter unknown is not allowed.',
                'source' => ['parameter' => 'filter'],
            ]
        );

        $response = $this->actingAs($this->user)->jsonApi()
            ->withHeader('Locale', 'es')
            ->get($url);

        // Bad Request
        $response->assertError(
            400,
            [
                'title' => 'Parámetro de Consulta No Válido',
                'detail' => 'El parámetro de fitro unknown no está permido.',
                'source' => ['parameter' => 'filter'],
            ]
        );
    }

    /** @test */
    public function can_search_variations_by_name()
    {
        Variation::factory()->for($this->workout)->count(3)
            ->state(
                new Sequence(
                    ['name' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_NAME],
                    ['name' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_NAME],
                    ['name' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_NAME],
                )
            )
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

    /** @test */
    public function can_search_variations_by_performance()
    {
        Variation::factory()->for($this->workout)->count(3)
            ->state(
                new Sequence(
                    ['performance' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_PERFORMANCE],
                    ['performance' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_PERFORMANCE],
                    ['performance' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_PERFORMANCE],
                )
            )
            ->create();

        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                self::MODEL_FILTER_SEARCH_PARAM_NAME => 'alfa'
            ]
        );

        $this->actingAs($this->user)->jsonApi()->get($url)
            ->assertJsonCount(1, 'data')
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_PERFORMANCE)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_PERFORMANCE)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_PERFORMANCE);
    }

    /** @test */
    public function can_search_variations_by_name_with_multiple_terms()
    {
        Variation::factory()->for($this->workout)->count(3)
            ->state(
                new Sequence(
                    ['name' => self::MODEL_EXTRA_SEARCHING_TERM . ' ' . self::MODEL_ALFA_NAME],
                    ['name' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_PI_NAME],
                    ['name' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_JI_NAME],
                )
            )
            ->create();

        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                self::MODEL_FILTER_SEARCH_PARAM_NAME => self::MODEL_MULTIPLE_SEARCH_TERM
            ]
        );

        $this->actingAs($this->user)->jsonApi()->get($url)
            ->assertJsonCount(2, 'data')
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_PI_NAME)
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_JI_NAME)
            ->assertDontSee(self::MODEL_PLURAL_NAME . ' ' . self::MODEL_ALFA_NAME);
    }

    /** @test */
    public function can_search_variations_by_performance_with_multiple_terms()
    {
        Variation::factory()->for($this->workout)->count(3)
            ->state(
                new Sequence(
                    ['performance' => self::MODEL_EXTRA_SEARCHING_TERM . ' ' . self::MODEL_ALFA_PERFORMANCE],
                    ['performance' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_PI_NAME],
                    ['performance' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_JI_NAME],
                )
            )
            ->create();

        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                self::MODEL_FILTER_SEARCH_PARAM_NAME => self::MODEL_MULTIPLE_SEARCH_TERM
            ]
        );

        $this->actingAs($this->user)->jsonApi()->get($url)
            ->assertJsonCount(2, 'data')
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_PI_NAME)
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_JI_NAME)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_PERFORMANCE);
    }
}
