<?php

namespace Tests\Feature\Equipments;

use App\Models\Equipment;
use App\Models\User;
use Database\Seeders\PermissionsSeeders\EquipmentsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UpdateEquipmentsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'equipments';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.update';

    const MODEL_ATTRIBUTE_NAME = 'name';
    const MODEL_ATTRIBUTE_DESCRIPTION = 'description';

    const MODEL_NAME_ATTRIBUTE_VALUE = 'name changed';
    const MODEL_DESCRIPTION_ATTRIBUTE_VALUE = 'description changed';

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
    public function guests_users_cannot_update_equipments()
    {
        $equipment = Equipment::factory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $equipment->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_NAME_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_DESCRIPTION => self::MODEL_DESCRIPTION_ATTRIBUTE_VALUE,
            ]
        ];

        $response = $this->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $equipment->getRouteKey()));

        // Unauthorized (401)
        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_users_as_admin_can_update_equipments()
    {
        $equipment = Equipment::factory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $equipment->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_NAME_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_DESCRIPTION => self::MODEL_DESCRIPTION_ATTRIBUTE_VALUE,
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->withHeader('Authorization', $this->token)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $equipment->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                'id' => $equipment->getRouteKey(),
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_NAME_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_DESCRIPTION => self::MODEL_DESCRIPTION_ATTRIBUTE_VALUE,
            ]
        );
    }

    /** @test */
    public function can_update_the_equipments_name_only()
    {
        $equipment = Equipment::factory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $equipment->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_NAME_ATTRIBUTE_VALUE,
            ]
        ];

        $response = $this->actingAs($this->user)
            ->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->withHeader('Authorization', $this->token)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $equipment->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                'id' => $equipment->getRouteKey(),
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_NAME_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_DESCRIPTION => $equipment->description,
            ]
        );
    }

    /** @test */
    public function can_update_the_equipments_description_only()
    {
        $equipment = Equipment::factory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $equipment->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_DESCRIPTION => self::MODEL_DESCRIPTION_ATTRIBUTE_VALUE,
            ]
        ];

        $response = $this->actingAs($this->user)
            ->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->withHeader('Authorization', $this->token)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $equipment->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                'id' => $equipment->getRouteKey(),
                self::MODEL_ATTRIBUTE_NAME => $equipment->name,
                self::MODEL_ATTRIBUTE_DESCRIPTION => self::MODEL_DESCRIPTION_ATTRIBUTE_VALUE,
            ]
        );
    }

    /** @test */
    public function cannot_update_the_equipments_name_if_exists()
    {
        $equipment = Equipment::factory()->create();
        $equipment2 = Equipment::factory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $equipment->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME => $equipment2->name,
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->withHeader('Authorization', $this->token)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $equipment->getRouteKey()));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => '/data/attributes/name'],
                'detail' => 'The name has already been taken.'
            ]
        );
    }
}
