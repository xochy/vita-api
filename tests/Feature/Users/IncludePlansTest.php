<?php

namespace Tests\Feature\Users;

use App\Models\Plan;
use App\Models\User;
use Database\Seeders\permissionsSeeders\PlansPermissionsSeeder;
use Database\Seeders\permissionsSeeders\UsersPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IncludePlansTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'users';
    const MODEL_INCLUDE_RELATIONSHIP_NAME = 'plan';
    const MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME = 'plans';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';

    const MODEL_RELATED_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME
        . '.' . self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME;

    const MODEL_SELF_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME
        . '.' . self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME . '.show';

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(UsersPermissionsSeeder::class);
            $this->seed(PlansPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function users_can_include_plans()
    {
        $user = User::factory()
            ->has(Plan::factory()->forGoal()->forFrequency()->forPhysicalCondition())
            ->create()->assignRole('user');

        $response = $this->actingAs($user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME)
            ->get(route(self::MODEL_MAIN_ACTION_ROUTE, $user));

        $response->assertJsonFragment([
            'related' => route(self::MODEL_RELATED_ROUTE, $user)
        ]);

        $response->assertJsonFragment([
            'self' => route(self::MODEL_SELF_ROUTE, $user)
        ]);

        $this->assertDatabaseCount('plan_user', 1);
    }

    /** @test */
    public function users_can_fetch_related_plans()
    {
        $user = User::factory()
            ->has(Plan::factory()->forGoal()->forFrequency()->forPhysicalCondition()->count(3))
            ->create()->assignRole('user');

        $response = $this->actingAs($user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME)
            ->get(route(self::MODEL_MAIN_ACTION_ROUTE, $user));

        $response->assertJsonFragment([
            'related' => route(self::MODEL_RELATED_ROUTE, $user)
        ]);

        $response->assertJsonFragment([
            'self' => route(self::MODEL_SELF_ROUTE, $user)
        ]);

        $this->assertDatabaseCount('plan_user', 3);

        $response->assertSee($user->plans[0]->name);
        $response->assertSee($user->plans[1]->name);
        $response->assertSee($user->plans[2]->name);

        $this->assertDatabaseHas('plan_user', [
            'plan_id' => $user->plans[0]->id,
            'user_id' => $user->id
        ]);

        $this->assertDatabaseHas('plan_user', [
            'plan_id' => $user->plans[1]->id,
            'user_id' => $user->id
        ]);

        $this->assertDatabaseHas('plan_user', [
            'plan_id' => $user->plans[2]->id,
            'user_id' => $user->id
        ]);
    }
}
