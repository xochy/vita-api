<?php

namespace Tests\Feature\Permissions;

use App\Models\User;
use Database\Seeders\permissionsSeeders\PermissionsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SortPermissionsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'permissions';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.index';

    const MODEL_ALFA_NAME = 'read permissions';
    const MODEL_BETA_NAME = 'create permissions';
    const MODEL_GAMA_NAME = 'update permissions';
    const MODEL_DELTA_NAME = 'delete permissions';
    const MODEL_EPSILON_NAME = 'restore permissions';
    const MODEL_ZETA_NAME = 'force delete permissions';

    const MODEL_SORT_PARAM_VALUE = 'name';

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(PermissionsPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('superAdmin');
    }

    /** @test */
    public function can_sort_permissions_by_name_asc()
    {
        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                'sort' => self::MODEL_SORT_PARAM_VALUE
            ]
        );

        $this->actingAs($this->user)->jsonApi()
            ->get($url)->assertSeeInOrder(
                [
                    self::MODEL_BETA_NAME,
                    self::MODEL_DELTA_NAME,
                    self::MODEL_ZETA_NAME,
                    self::MODEL_ALFA_NAME,
                    self::MODEL_EPSILON_NAME,
                    self::MODEL_GAMA_NAME,
                ]
            );
    }

    /** @test */
    public function can_sort_permissions_by_name_desc()
    {
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
                    self::MODEL_EPSILON_NAME,
                    self::MODEL_ALFA_NAME,
                    self::MODEL_ZETA_NAME,
                    self::MODEL_DELTA_NAME,
                    self::MODEL_BETA_NAME,
                ]
            );
    }

    /** @test */
    public function cannot_sort_permissions_by_unknown_fields()
    {
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
