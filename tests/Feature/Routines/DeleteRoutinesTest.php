<?php

namespace Tests\Feature\Routines;

use App\Models\Routine;
use App\Models\User;
use Database\Seeders\permissionsSeeders\RoutinesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DeleteRoutinesTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'routines';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.'. self::MODEL_PLURAL_NAME .'.destroy';

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(RoutinesPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('user');
    }

    /** @test */
    public function guests_users_cannot_delete_routines()
    {
        $routine = Routine::factory()->create();

        $response = $this->jsonApi()
            ->delete(route(self::MODEL_MAIN_ACTION_ROUTE, $routine->getRouteKey()));

        // Unauthorized (401)
        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_users_can_delete_workouts()
    {
        $routine = Routine::factory()->create();

        $response = $this->actingAs($this->user)
            ->jsonApi()
            ->delete(route(self::MODEL_MAIN_ACTION_ROUTE, $routine->getRouteKey()));

        // No Content (204)
        $response->assertStatus(204);
        $response->assertNoContent();

        $this->assertDatabaseMissing('routines', [
            'id' => $routine->id,
        ]);
    }
}
