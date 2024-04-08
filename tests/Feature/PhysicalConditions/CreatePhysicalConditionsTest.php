<?php

namespace Tests\Feature\PhysicalConditions;

use App\Models\PhysicalCondition;
use App\Models\User;
use Database\Seeders\permissionsSeeders\PhysicalConditionsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CreatePhysicalConditionsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_TABLE_NAME = 'physical_conditions';
    const MODEL_PLURAL_NAME = 'physical-conditions';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.store';

    const MODEL_ATTRIBUTE_NAME = 'name';
    const MODEL_ATTRIBUTE_DESCRIPTION = 'description';

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(PhysicalConditionsPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function guests_users_cannot_create_physical_conditions()
    {
        $physicalCondition = array_filter(PhysicalCondition::factory()->raw());

        $response = $this->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withData([
                'type' => self::MODEL_PLURAL_NAME,
                'attributes' => $physicalCondition
            ])
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unauthorized (401)
        $response->assertStatus(401);

        $this->assertDatabaseMissing(self::MODEL_TABLE_NAME, $physicalCondition);
    }

    /** @test */
    public function authenticated_users_as_admin_can_create_physical_conditions()
    {
        $physicalCondition = array_filter(PhysicalCondition::factory()->raw());

        $data = [
            'type'       => self::MODEL_PLURAL_NAME,
            'attributes' => $physicalCondition
        ];

        $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE))
            ->assertCreated();

        $this->assertDatabaseHas(self::MODEL_TABLE_NAME, [
            self::MODEL_ATTRIBUTE_NAME        => $physicalCondition[self::MODEL_ATTRIBUTE_NAME],
            self::MODEL_ATTRIBUTE_DESCRIPTION => $physicalCondition[self::MODEL_ATTRIBUTE_DESCRIPTION],
        ]);
    }

    /** @test */
    public function physical_condition_name_is_required()
    {
        $physicalCondition = PhysicalCondition::factory()->raw(['name' => '']);

        $data = [
            'type'       => self::MODEL_PLURAL_NAME,
            'attributes' => $physicalCondition
        ];

        $response = $this->actingAs($this->user)->jsonApi()
        ->expects(self::MODEL_PLURAL_NAME)->withData($data)
        ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(422, [
            'source' => ['pointer' => '/data/attributes/name'],
            'detail' => 'The name field is required.'
        ]);

        $response->assertSee('data\/attributes\/name');

        $this->assertDatabaseMissing(self::MODEL_TABLE_NAME, $physicalCondition);
    }

    /** @test */
    public function physical_condition_name_must_be_unique()
    {
        $physicalCondition = PhysicalCondition::factory()->create();

        $data = [
            'type'       => self::MODEL_PLURAL_NAME,
            'attributes' => array_filter(PhysicalCondition::factory()->raw([
                'name' => $physicalCondition->name
            ]))
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(422, [
            'source' => ['pointer' => '/data/attributes/name'],
            'detail' => 'The name has already been taken.'
        ]);

        $response->assertSee('data\/attributes\/name');

        $this->assertDatabaseCount(self::MODEL_TABLE_NAME, 1);
    }

    /** @test */
    public function physical_condition_description_is_required()
    {
        $physicalCondition = PhysicalCondition::factory()->raw([self::MODEL_ATTRIBUTE_DESCRIPTION => '']);

        $data = [
            'type'       => self::MODEL_PLURAL_NAME,
            'attributes' => $physicalCondition
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(422, [
            'source' => ['pointer' => '/data/attributes/description'],
            'detail' => 'The description field is required.'
        ]);

        $response->assertSee('data\/attributes\/description');

        $this->assertDatabaseMissing(self::MODEL_TABLE_NAME, $physicalCondition);
    }

    /** @test */
    public function physical_condition_description_must_be_a_string()
    {
        $physicalCondition = PhysicalCondition::factory()->raw([self::MODEL_ATTRIBUTE_DESCRIPTION => 123]);

        $data = [
            'type'       => self::MODEL_PLURAL_NAME,
            'attributes' => $physicalCondition
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(422, [
            'source' => ['pointer' => '/data/attributes/description'],
            'detail' => 'The description field must be a string.'
        ]);

        $response->assertSee('data\/attributes\/description');

        $this->assertDatabaseMissing(self::MODEL_TABLE_NAME, $physicalCondition);
    }
}
