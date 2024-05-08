<?php

namespace Tests\Feature\Workouts;

use App\Models\Subcategory;
use App\Models\User;
use App\Models\Workout;
use Database\Seeders\permissionsSeeders\WorkoutsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DeleteWorkoutsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'workouts';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.destroy';

    protected User $user;
    protected Subcategory $subcategory;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(WorkoutsPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
        $this->subcategory = Subcategory::factory()->forCategory()->create();
    }

    /** @test */
    public function guests_users_cannot_delete_workouts()
    {
        $workout = Workout::factory()->for($this->subcategory)->create();

        $response = $this->jsonApi()
            ->delete(route(self::MODEL_MAIN_ACTION_ROUTE, $workout->getRouteKey()));

        // Unauthorized (401)
        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_users_as_admin_can_delete_workouts()
    {
        $workout = Workout::factory()->for($this->subcategory)->create();

        $response = $this->actingAs($this->user)
            ->jsonApi()
            ->delete(route(self::MODEL_MAIN_ACTION_ROUTE, $workout->getRouteKey()));

        // No Content (204)
        $response->assertStatus(204);
        $response->assertNoContent();

        $this->assertDatabaseMissing(
            self::MODEL_PLURAL_NAME,
            [
                'id' => $workout->getKey()
            ]
        );
    }
}
