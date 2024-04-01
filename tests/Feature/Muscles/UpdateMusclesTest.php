<?php

namespace Tests\Feature\Muscles;

use App\Models\Muscle;
use App\Models\User;
use Database\Seeders\permissionsSeeders\MusclesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UpdateMusclesTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'muscles';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.update';

    const MODEL_ATTRIBUTE_NAME = 'name';
    const MODEL_ATTRIBUTE_DESCRIPTION = 'description';

    const MODEL_NAME_ATTRIBUTE_VALUE = 'name changed';
    const MODEL_DESCRIPTION_ATTRIBUTE_VALUE = 'description changed';

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(MusclesPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function guests_users_cannot_update_muscles()
    {
        $muscle = Muscle::factory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $muscle->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_NAME_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_DESCRIPTION => self::MODEL_DESCRIPTION_ATTRIBUTE_VALUE,
            ]
        ];

        $response = $this->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $muscle->getRouteKey()));

        // Unauthorized (401)
        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_users_as_admin_can_update_muscles()
    {
        $muscle = Muscle::factory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $muscle->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_NAME_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_DESCRIPTION => self::MODEL_DESCRIPTION_ATTRIBUTE_VALUE,
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $muscle->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas(self::MODEL_PLURAL_NAME, [
            'id' => $muscle->getRouteKey(),
            self::MODEL_ATTRIBUTE_NAME => self::MODEL_NAME_ATTRIBUTE_VALUE,
            self::MODEL_ATTRIBUTE_DESCRIPTION => self::MODEL_DESCRIPTION_ATTRIBUTE_VALUE,
        ]);
    }

    /** @test */
    public function can_update_the_muscle_name_only()
    {
        $muscle = Muscle::factory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $muscle->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_NAME_ATTRIBUTE_VALUE,
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $muscle->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas(self::MODEL_PLURAL_NAME, [
            'id' => $muscle->getRouteKey(),
            self::MODEL_ATTRIBUTE_NAME => self::MODEL_NAME_ATTRIBUTE_VALUE,
            self::MODEL_ATTRIBUTE_DESCRIPTION => $muscle->description,
        ]);
    }

    /** @test */
    public function can_update_the_muscle_description_only()
    {
        $muscle = Muscle::factory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $muscle->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_DESCRIPTION => self::MODEL_DESCRIPTION_ATTRIBUTE_VALUE,
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $muscle->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas(self::MODEL_PLURAL_NAME, [
            'id' => $muscle->getRouteKey(),
            self::MODEL_ATTRIBUTE_NAME => $muscle->name,
            self::MODEL_ATTRIBUTE_DESCRIPTION => self::MODEL_DESCRIPTION_ATTRIBUTE_VALUE,
        ]);
    }

    /** @test */
    public function cannot_update_the_muscle_name_if_exists()
    {
        $muscle = Muscle::factory()->create();
        $muscle2 = Muscle::factory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $muscle->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME => $muscle2->name,
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $muscle->getRouteKey()));

        // Unprocessable Entity (422)
        $response->assertError(422, [
            'source' => ['pointer' => '/data/attributes/name'],
            'detail' => 'The name has already been taken.'
        ]);

        $response->assertSee('data\/attributes\/name');
    }
}
