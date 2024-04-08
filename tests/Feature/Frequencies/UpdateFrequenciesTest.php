<?php

namespace Tests\Feature\Frequencies;

use App\Models\Frequency;
use App\Models\User;
use Database\Seeders\permissionsSeeders\FrequenciesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UpdateFrequenciesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    const MODEL_PLURAL_NAME = 'frequencies';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.update';

    const MODEL_ATTRIBUTE_NAME = 'name';
    const MODEL_ATTRIBUTE_DESCRIPTION = 'description';

    const MODEL_NAME_ATTRIBUTE_VALUE = 'name changed';
    const MODEL_DESCRIPTION_ATTRIBUTE_VALUE = 'description changed';

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(FrequenciesPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function guests_users_cannot_update_frequencies()
    {
        $frequency = Frequency::factory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $frequency->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_NAME_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_DESCRIPTION => self::MODEL_DESCRIPTION_ATTRIBUTE_VALUE,
            ]
        ];

        $response = $this->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $frequency->getRouteKey()));

        // Unauthorized (401)
        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_users_as_admin_can_update_frequencies()
    {
        $frequency = Frequency::factory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $frequency->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_NAME_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_DESCRIPTION => self::MODEL_DESCRIPTION_ATTRIBUTE_VALUE,
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $frequency->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas(self::MODEL_PLURAL_NAME, [
            'id' => $frequency->getRouteKey(),
            self::MODEL_ATTRIBUTE_NAME => self::MODEL_NAME_ATTRIBUTE_VALUE,
            self::MODEL_ATTRIBUTE_DESCRIPTION => self::MODEL_DESCRIPTION_ATTRIBUTE_VALUE,
        ]);
    }

    /** @test */
    public function can_update_the_frequency_name_only()
    {
        $frequency = Frequency::factory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $frequency->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_NAME_ATTRIBUTE_VALUE,
            ]
        ];

        $response = $this->actingAs($this->user)
            ->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $frequency->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas(self::MODEL_PLURAL_NAME, [
            'id' => $frequency->getRouteKey(),
            self::MODEL_ATTRIBUTE_NAME => self::MODEL_NAME_ATTRIBUTE_VALUE,
            self::MODEL_ATTRIBUTE_DESCRIPTION => $frequency->description,
        ]);
    }

    /** @test */
    public function can_update_the_frequency_description_only()
    {
        $frequency = Frequency::factory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $frequency->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_DESCRIPTION => self::MODEL_DESCRIPTION_ATTRIBUTE_VALUE,
            ]
        ];

        $response = $this->actingAs($this->user)
            ->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $frequency->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas(self::MODEL_PLURAL_NAME, [
            'id' => $frequency->getRouteKey(),
            self::MODEL_ATTRIBUTE_NAME => $frequency->name,
            self::MODEL_ATTRIBUTE_DESCRIPTION => self::MODEL_DESCRIPTION_ATTRIBUTE_VALUE,
        ]);
    }

    /** @test */
    public function cannot_update_the_frequency_name_if_exists()
    {
        $frequency = Frequency::factory()->create();
        $frequency2 = Frequency::factory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $frequency->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME => $frequency2->name,
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $frequency->getRouteKey()));

        // Unprocessable Entity (422)
        $response->assertError(422, [
            'source' => ['pointer' => '/data/attributes/name'],
            'detail' => 'The name has already been taken.'
        ]);

        $response->assertSee('data\/attributes\/name');
    }
}
