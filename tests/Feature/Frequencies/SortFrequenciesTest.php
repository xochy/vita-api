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

class SortFrequenciesTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'frequencies';
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
            $this->seed(FrequenciesPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function can_sort_frequencies_by_name_asc()
    {
        Frequency::factory()->count(3)
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
            ->expects(self::MODEL_PLURAL_NAME)
            ->get($url)
            ->assertJsonPath('data.0.attributes.name', self::MODEL_ALFA_NAME)
            ->assertJsonPath('data.1.attributes.name', self::MODEL_BETA_NAME)
            ->assertJsonPath('data.2.attributes.name', self::MODEL_GAMA_NAME);
    }

    /** @test */
    public function can_sort_frequencies_by_name_desc()
    {
        Frequency::factory()->count(3)
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
            ->expects(self::MODEL_PLURAL_NAME)
            ->get($url)
            ->assertJsonPath('data.0.attributes.name', self::MODEL_GAMA_NAME)
            ->assertJsonPath('data.1.attributes.name', self::MODEL_BETA_NAME)
            ->assertJsonPath('data.2.attributes.name', self::MODEL_ALFA_NAME);
    }

    /** @test */
    public function cannot_sort_frequencies_by_unknown_fields()
    {
        Frequency::factory()->count(3)->create();

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
