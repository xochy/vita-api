<?php

namespace Tests\Feature\Plans;

use App\Models\Plan;
use App\Models\User;
use Database\Seeders\permissionsSeeders\UsersPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IncludeUsersTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'plans';
    const MODEL_INCLUDE_RELATIONSHIP_NAME = 'user';
    const MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME = 'users';
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
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function plans_can_include_users()
    {
        $plan = Plan::factory()->forGoal()->forFrequency()->forPhysicalCondition()
            ->hasAttached(User::factory())
            ->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME)
            ->get(route(self::MODEL_MAIN_ACTION_ROUTE, $plan));

        $response->assertJsonFragment(
            [
                'related' => route(self::MODEL_RELATED_ROUTE, $plan)
            ]
        );

        $response->assertJsonFragment(
            [
                'self' => route(self::MODEL_SELF_ROUTE, $plan)
            ]
        );

        $this->assertDatabaseCount('plan_user', 1);

        $this->assertDatabaseHas(
            'plan_user',
            [
                'plan_id' => $plan->id,
                'user_id' => $plan->users->first()->id
            ]
        );
    }

    /** @test */
    public function plans_can_fetch_related_users()
    {
        $plan = Plan::factory()->forGoal()->forFrequency()->forPhysicalCondition()
            ->hasAttached(User::factory())
            ->hasAttached(User::factory())
            ->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME)
            ->get(route(self::MODEL_MAIN_ACTION_ROUTE, $plan));

        $response->assertJsonFragment(
            [
                'related' => route(self::MODEL_RELATED_ROUTE, $plan)
            ]
        );

        $response->assertJsonFragment(
            [
                'self' => route(self::MODEL_SELF_ROUTE, $plan)
            ]
        );

        $this->assertDatabaseCount('plan_user', 2);

        $this->assertDatabaseHas(
            'plan_user',
            [
                'plan_id' => $plan->id,
                'user_id' => $plan->users[0]->id
            ]
        );

        $this->assertDatabaseHas(
            'plan_user',
            [
                'plan_id' => $plan->id,
                'user_id' => $plan->users[1]->id
            ]
        );
    }
}
