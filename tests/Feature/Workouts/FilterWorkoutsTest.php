<?php

namespace Tests\Feature\Workouts;

use App\Models\Category;
use App\Models\User;
use App\Models\Workout;
use Database\Seeders\permissionsSeeders\WorkoutsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FilterWorkoutsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_SINGLE_NAME = 'workout';
    const MODEL_PLURAL_NAME = 'workouts';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.index';

    const MODEL_BETA_NAME = 'beta name';
    const MODEL_ALFA_NAME = 'alfa name';
    const MODEL_GAMA_NAME = 'gama name';

    const MODEL_GAMA_PERFORMANCE = 'gama performance';
    const MODEL_BETA_PERFORMANCE = 'beta performance';
    const MODEL_ALFA_PERFORMANCE = 'alfa performance';

    const MODEL_GAMA_COMMENTS = 'gama comments';
    const MODEL_BETA_COMMENTS = 'beta comments';
    const MODEL_ALFA_COMMENTS = 'alfa comments';

    const MODEL_GAMA_CORRECTIONS = 'gama corrections';
    const MODEL_BETA_CORRECTIONS = 'beta corrections';
    const MODEL_ALFA_CORRECTIONS = 'alfa corrections';

    const MODEL_GAMA_WARNINGS = 'gama warnings';
    const MODEL_BETA_WARNINGS = 'beta warnings';
    const MODEL_ALFA_WARNINGS = 'alfa warnings';

    const MODEL_PI_NAME = 'pi lambda name';
    const MODEL_JI_NAME = 'ji lambda name';

    const MODEL_EXTRA_SEARCHING_TERM = 'omega';
    const MODEL_MULTIPLE_SEARCH_TERM = self::MODEL_SINGLE_NAME . ' ' . 'lambda';

    const MODEL_FILTER_NAME_PARAM_NAME = 'filter[name]';
    const MODEL_FILTER_SEARCH_PARAM_NAME = 'filter[search]';
    const MODEL_FILTER_UNKNOWN_PARAM_NAME = 'filter[unknown]';
    const MODEL_FILTER_PERFORMANCE_PARAM_NAME = 'filter[performance]';
    const MODEL_FILTER_COMMENTS_PARAM_NAME = 'filter[comments]';
    const MODEL_FILTER_CORRECTIONS_PARAM_NAME = 'filter[corrections]';
    const MODEL_FILTER_WARNINGS_PARAM_NAME = 'filter[warnings]';

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(WorkoutsPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function can_filter_workouts_by_name()
    {
        Workout::factory()->forCategory()->count(3)
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
    public function can_filter_workouts_by_performance()
    {
        Workout::factory()->forCategory()->count(3)
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
    public function can_filter_workouts_by_comments()
    {
        Workout::factory()->forCategory()->count(3)
            ->state(
                new Sequence(
                    ['comments' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_COMMENTS],
                    ['comments' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_COMMENTS],
                    ['comments' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_COMMENTS],
                )
            )
            ->create();

        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                self::MODEL_FILTER_COMMENTS_PARAM_NAME => 'alfa'
            ]
        );

        $this->actingAs($this->user)->jsonApi()->get($url)
            ->assertJsonCount(1, 'data')
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_COMMENTS)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_COMMENTS)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_COMMENTS);
    }

    /** @test */
    public function can_filter_workouts_by_corrections()
    {
        Workout::factory()->forCategory()->count(3)
            ->state(
                new Sequence(
                    ['corrections' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_CORRECTIONS],
                    ['corrections' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_CORRECTIONS],
                    ['corrections' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_CORRECTIONS],
                )
            )
            ->create();

        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                self::MODEL_FILTER_CORRECTIONS_PARAM_NAME => 'alfa'
            ]
        );

        $this->actingAs($this->user)->jsonApi()->get($url)
            ->assertJsonCount(1, 'data')
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_CORRECTIONS)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_CORRECTIONS)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_CORRECTIONS);
    }

    /** @test */
    public function can_filter_workouts_by_warnings()
    {
        Workout::factory()->forCategory()->count(3)
            ->state(
                new Sequence(
                    ['warnings' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_WARNINGS],
                    ['warnings' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_WARNINGS],
                    ['warnings' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_WARNINGS],
                )
            )
            ->create();

        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                self::MODEL_FILTER_WARNINGS_PARAM_NAME => 'alfa'
            ]
        );

        $this->actingAs($this->user)->jsonApi()->get($url)
            ->assertJsonCount(1, 'data')
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_WARNINGS)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_WARNINGS)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_WARNINGS);
    }

    /** @test */
    public function can_filter_workouts_by_all_terms()
    {
        Workout::factory()->forCategory()->count(3)
            ->state(
                new Sequence(
                    [
                        'name' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_NAME,
                        'performance' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_PERFORMANCE,
                        'comments' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_COMMENTS,
                        'corrections' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_CORRECTIONS,
                        'warnings' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_WARNINGS,
                    ],
                    [
                        'name' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_NAME,
                        'performance' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_PERFORMANCE,
                        'comments' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_COMMENTS,
                        'corrections' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_CORRECTIONS,
                        'warnings' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_WARNINGS,
                    ],
                    [
                        'name' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_NAME,
                        'performance' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_PERFORMANCE,
                        'comments' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_COMMENTS,
                        'corrections' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_CORRECTIONS,
                        'warnings' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_WARNINGS,
                    ],
                )
            )
            ->create();

        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                self::MODEL_FILTER_NAME_PARAM_NAME => 'alfa',
                self::MODEL_FILTER_PERFORMANCE_PARAM_NAME => 'alfa',
                self::MODEL_FILTER_COMMENTS_PARAM_NAME => 'alfa',
                self::MODEL_FILTER_CORRECTIONS_PARAM_NAME => 'alfa',
                self::MODEL_FILTER_WARNINGS_PARAM_NAME => 'alfa',
            ]
        );

        $this->actingAs($this->user)->jsonApi()->get($url)
            ->assertJsonCount(1, 'data')
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_NAME)
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_PERFORMANCE)
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_COMMENTS)
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_CORRECTIONS)
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_WARNINGS)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_NAME)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_PERFORMANCE)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_COMMENTS)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_CORRECTIONS)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_WARNINGS)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_NAME)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_PERFORMANCE)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_COMMENTS)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_CORRECTIONS)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_WARNINGS);
    }

    /** @test */
    public function cannot_filter_workouts_by_unknown_filters()
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
    public function can_search_workouts_by_name()
    {
        Workout::factory()->forCategory()->count(3)
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
    public function can_search_workouts_by_performance()
    {
        Workout::factory()->forCategory()->count(3)
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
    public function can_search_workouts_by_comments()
    {
        Workout::factory()->forCategory()->count(3)
            ->state(
                new Sequence(
                    ['comments' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_COMMENTS],
                    ['comments' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_COMMENTS],
                    ['comments' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_COMMENTS],
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
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_COMMENTS)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_COMMENTS)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_COMMENTS);
    }

    /** @test */
    public function can_search_workouts_by_corrections()
    {
        Workout::factory()->forCategory()->count(3)
            ->state(
                new Sequence(
                    ['corrections' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_CORRECTIONS],
                    ['corrections' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_CORRECTIONS],
                    ['corrections' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_CORRECTIONS],
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
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_CORRECTIONS)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_CORRECTIONS)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_CORRECTIONS);
    }

    /** @test */
    public function can_search_workouts_by_warnings()
    {
        Workout::factory()->forCategory()->count(3)
            ->state(
                new Sequence(
                    ['warnings' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_WARNINGS],
                    ['warnings' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_WARNINGS],
                    ['warnings' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_WARNINGS],
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
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_WARNINGS)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_WARNINGS)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_WARNINGS);
    }

    /** @test */
    public function can_search_workouts_by_name_with_multiple_terms()
    {
        Workout::factory()->forCategory()->count(3)
            ->state(
                new Sequence(
                    ['name' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_PI_NAME],
                    ['name' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_NAME],
                    ['name' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_JI_NAME],
                )
            )
            ->create();

        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                self::MODEL_FILTER_SEARCH_PARAM_NAME => 'lambda'
            ]
        );

        $this->actingAs($this->user)->jsonApi()->get($url)
            ->assertJsonCount(2, 'data')
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_PI_NAME)
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_JI_NAME)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_NAME);
    }
}
