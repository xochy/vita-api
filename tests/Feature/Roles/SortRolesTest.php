<?php

namespace Tests\Feature\Roles;

use App\Models\User;
use Database\Seeders\permissionsSeeders\RolesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SortRolesTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'roles';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.index';

    const MODEL_ALFA_NAME = 'admin';
    const MODEL_BETA_NAME = 'superAdmin';
    const MODEL_GAMA_NAME = 'user';

    const MODEL_SORT_PARAM_VALUE = 'name';

    protected User $user;
    protected string $token;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(RolesPermissionsSeeder::class);
        }

        [$this->user, $this->token] = $this->createUserWithToken('superAdmin');
    }

    /** @test */
    public function can_sort_roles_by_name_asc()
    {
        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                'sort' => self::MODEL_SORT_PARAM_VALUE
            ]
        );

        $this->actingAs($this->user)->jsonApi()
            ->withHeader('Authorization', $this->token)
            ->get($url)->assertSeeInOrder(
                [
                    self::MODEL_ALFA_NAME,
                    self::MODEL_BETA_NAME,
                    self::MODEL_GAMA_NAME,
                ]
            );
    }

    /** @test */
    public function can_sort_roles_by_name_desc()
    {
        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                'sort' => '-' . self::MODEL_SORT_PARAM_VALUE
            ]
        );

        $this->actingAs($this->user)->jsonApi()
            ->withHeader('Authorization', $this->token)
            ->get($url)->assertSeeInOrder(
                [
                    self::MODEL_GAMA_NAME,
                    self::MODEL_BETA_NAME,
                    self::MODEL_ALFA_NAME,
                ]
            );
    }

    /** @test */
    public function cannot_sort_roles_by_unknown_fields()
    {
        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                'sort' => 'unknown_field'
            ]
        );

        $response = $this->actingAs($this->user)->jsonApi()
            ->withHeader('Authorization', $this->token)
            ->get($url);

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
