<?php

namespace Tests\Feature\Goals;

use App\Models\Goal;
use App\Models\User;
use Database\Seeders\permissionsSeeders\GoalsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DeleteGoalsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    const MODEL_PLURAL_NAME = 'goals';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.destroy';

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(GoalsPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function guests_users_cannot_delete_goals()
    {
        $goal = Goal::factory()->create();

        $response = $this->jsonApi()
            ->delete(route(self::MODEL_MAIN_ACTION_ROUTE, $goal->getRouteKey()));

        // Unauthorized (401)
        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_users_as_admin_can_delete_goals()
    {
        $goal = Goal::factory()->create();

        $response = $this->actingAs($this->user)
            ->jsonApi()
            ->delete(route(self::MODEL_MAIN_ACTION_ROUTE, $goal->getRouteKey()));

        // No Content (204)
        $response->assertStatus(204);
        $response->assertNoContent();

        $this->assertDatabaseMissing(
            self::MODEL_PLURAL_NAME,
            [
                'id' => $goal->getKey()
            ]
        );
    }
}
