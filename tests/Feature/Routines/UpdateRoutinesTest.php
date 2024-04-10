<?php

namespace Tests\Feature\Routines;

use App\Models\Routine;
use App\Models\User;
use Database\Seeders\permissionsSeeders\RoutinesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UpdateRoutinesTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'routines';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.update';

    const MODEL_ATTRIBUTE_NAME = 'name';
    const MODEL_NAME_ATTRIBUTE_VALUE = 'name changed';

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
    public function guests_users_cannot_update_routines()
    {
        $routine = Routine::factory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $routine->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_NAME_ATTRIBUTE_VALUE,
            ]
        ];

        $response = $this->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $routine->getRouteKey()));

        // Unauthorized (401)
        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_users_can_update_routines()
    {
        $routine = Routine::factory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $routine->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_NAME_ATTRIBUTE_VALUE,
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $routine->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas('routines', [
            'id' => $routine->id,
            self::MODEL_ATTRIBUTE_NAME => self::MODEL_NAME_ATTRIBUTE_VALUE,
        ]);
    }
}
