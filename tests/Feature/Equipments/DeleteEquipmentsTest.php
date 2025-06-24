<?php

namespace Tests\Feature\Equipments;

use App\Models\Equipment;
use App\Models\User;
use Database\Seeders\PermissionsSeeders\EquipmentsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DeleteEquipmentsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'equipments';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.destroy';

    protected User $user;
    protected string $token;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(EquipmentsPermissionsSeeder::class);
        }

        [$this->user, $this->token] = $this->createUserWithToken();
    }

    /** @test */
    public function guests_users_cannot_delete_equipments()
    {
        $equipment = Equipment::factory()->create();

        $response = $this->jsonApi()
            ->delete(
                route(
                    self::MODEL_MAIN_ACTION_ROUTE,
                    $equipment->getRouteKey()
                )
            );

        // Unauthorized (401)
        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_users_as_admin_can_delete_equipments()
    {
        $equipment = Equipment::factory()->create();

        $response = $this->actingAs($this->user)
            ->jsonApi()
            ->withHeader('Authorization', $this->token)
            ->delete(
                route(
                    self::MODEL_MAIN_ACTION_ROUTE,
                    $equipment->getRouteKey()
                )
            );

        // No Content (204)
        $response->assertStatus(204);
        $response->assertNoContent();

        $this->assertDatabaseMissing(
            self::MODEL_PLURAL_NAME,
            [
                'id' => $equipment->getKey()
            ]
        );
    }
}
