<?php

namespace Tests\Feature\Directories;

use App\Models\Directory;
use App\Models\User;
use Database\Seeders\PermissionsSeeders\DirectoriesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FilterDirectoriesTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_SINGLE_NAME = 'directory';
    const MODEL_PLURAL_NAME = 'directories';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.index';

    const MODEL_BETA_NAME = 'beta name';
    const MODEL_ALFA_NAME = 'alfa name';
    const MODEL_GAMA_NAME = 'gama name';

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
            $this->seed(DirectoriesPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function can_filter_directories_by_name()
    {
        Directory::factory()->count(3)
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
    public function cannot_filter_directories_by_unknown_filters()
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
    public function can_search_categories_by_name()
    {
        Directory::factory()->count(3)
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
    public function can_search_categories_by_name_with_multiple_terms()
    {
        Directory::factory()->count(3)
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
}
