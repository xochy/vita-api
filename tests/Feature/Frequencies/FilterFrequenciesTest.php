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

class FilterFrequenciesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    const MODEL_SINGLE_NAME = 'frequency';
    const MODEL_PLURAL_NAME = 'frequencies';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.index';

    const MODEL_BETA_NAME = 'beta name';
    const MODEL_ALFA_NAME = 'alfa name';
    const MODEL_GAMA_NAME = 'gama name';

    const MODEL_GAMA_DESCRIPTION = 'gama description';
    const MODEL_BETA_DESCRIPTION = 'beta description';
    const MODEL_ALFA_DESCRIPTION = 'alfa description';

    const MODEL_PI_NAME = 'pi lambda name';
    const MODEL_JI_NAME = 'ji lambda name';

    const MODEL_MULTIPLE_SEARCH_TERM = self::MODEL_SINGLE_NAME . ' ' . 'lambda';

    const MODEL_FILTER_NAME_PARAM_NAME = 'filter[name]';
    const MODEL_FILTER_SEARCH_PARAM_NAME = 'filter[search]';
    const MODEL_FILTER_UNKNOWN_PARAM_NAME = 'filter[unknown]';
    const MODEL_FILTER_DESCRIPTION_PARAM_NAME = 'filter[description]';

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(FrequenciesPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function can_filter_frequencies_by_name()
    {
        Frequency::factory()->count(3)
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
    public function can_filter_frequencies_by_description()
    {
        Frequency::factory()->count(3)
            ->state(new Sequence(
                ['description' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_DESCRIPTION],
                ['description' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_DESCRIPTION],
                ['description' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_DESCRIPTION],
            ))
            ->create();

        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                self::MODEL_FILTER_DESCRIPTION_PARAM_NAME => 'alfa'
            ]
        );

        $this->actingAs($this->user)->jsonApi()->get($url)
            ->assertJsonCount(1, 'data')
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_DESCRIPTION)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_DESCRIPTION)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_DESCRIPTION);
    }

    /** @test */
    public function can_filter_frequencies_by_name_and_description()
    {
        Frequency::factory()->count(3)
            ->state(new Sequence(
                [
                    'name' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_NAME,
                    'description' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_DESCRIPTION
                ],
                [
                    'name' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_NAME,
                    'description' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_DESCRIPTION
                ],
                [
                    'name' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_NAME,
                    'description' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_DESCRIPTION
                ],
            ))
            ->create();

        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                self::MODEL_FILTER_NAME_PARAM_NAME => 'alfa',
                self::MODEL_FILTER_DESCRIPTION_PARAM_NAME => 'alfa'
            ]
        );

        $this->actingAs($this->user)->jsonApi()->get($url)
            ->assertJsonCount(1, 'data')
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_NAME)
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_DESCRIPTION)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_NAME)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_DESCRIPTION)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_NAME)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_DESCRIPTION);
    }

    /** @test */
    public function cannot_filter_frequencies_by_unknown_filters()
    {
        Frequency::factory()->create();

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
    public function can_search_frequencies_by_name()
    {
        Frequency::factory()->count(3)
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

    /** @test */
    public function can_search_frequencies_by_description()
    {
        Frequency::factory()->count(3)
            ->state(new Sequence(
                ['description' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_DESCRIPTION],
                ['description' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_DESCRIPTION],
                ['description' => self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_DESCRIPTION],
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
            ->assertSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_ALFA_DESCRIPTION)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_BETA_DESCRIPTION)
            ->assertDontSee(self::MODEL_SINGLE_NAME . ' ' . self::MODEL_GAMA_DESCRIPTION);
    }

    /** @test */
    public function can_search_frequencies_by_name_with_multiple_terms()
    {
        Frequency::factory()->count(3)
            ->state(new Sequence(
                ['name' => self::MODEL_PLURAL_NAME . ' ' . self::MODEL_ALFA_NAME],
                ['name' => self::MODEL_SINGLE_NAME . ' '. self::MODEL_PI_NAME],
                ['name' => self::MODEL_SINGLE_NAME . ' '. self::MODEL_JI_NAME],
            ))
            ->create();

        $url = route(self::MODEL_MAIN_ACTION_ROUTE,
            [
                self::MODEL_FILTER_SEARCH_PARAM_NAME => self::MODEL_MULTIPLE_SEARCH_TERM
            ]
        );

        $this->actingAs($this->user)->jsonApi()->get($url)
            ->assertJsonCount(2, 'data')
            ->assertSee(self::MODEL_SINGLE_NAME . ' '. self::MODEL_PI_NAME)
            ->assertSee(self::MODEL_SINGLE_NAME . ' '. self::MODEL_JI_NAME)
            ->assertDontSee( self::MODEL_PLURAL_NAME . ' ' . self::MODEL_ALFA_NAME);
    }
}
