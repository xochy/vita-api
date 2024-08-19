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

class SortVariationsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'variations';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.index';

    const MODEL_ALFA_NAME = 'alfa name';
    const MODEL_BETA_NAME = 'beta name';
    const MODEL_GAMA_NAME = 'gama name';

    const MODEL_SORT_PARAM_VALUE = 'name';

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
    public function can_sort_variations_by_name_asc()
    {
        Variation::factory()->for($this->workout)->count(3)
            ->state(
                new Sequence(
                    [self::MODEL_SORT_PARAM_VALUE => self::MODEL_GAMA_NAME],
                    [self::MODEL_SORT_PARAM_VALUE => self::MODEL_ALFA_NAME],
                    [self::MODEL_SORT_PARAM_VALUE => self::MODEL_BETA_NAME],
                )
            )
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
    public function can_sort_variations_by_name_desc()
    {
        Variation::factory()->for($this->workout)->count(3)
            ->state(
                new Sequence(
                    [self::MODEL_SORT_PARAM_VALUE => self::MODEL_ALFA_NAME],
                    [self::MODEL_SORT_PARAM_VALUE => self::MODEL_BETA_NAME],
                    [self::MODEL_SORT_PARAM_VALUE => self::MODEL_GAMA_NAME],
                )
            )
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
    public function cannot_sort_variations_by_unknown_fields()
    {
        Variation::factory()->for($this->workout)->count(3)->create();

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
