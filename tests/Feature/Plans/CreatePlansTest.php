<?php

namespace Tests\Feature\Plans;

use App\Models\Plan;
use App\Models\User;
use Database\Seeders\permissionsSeeders\PlansPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CreatePlansTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    const MODEL_PLURAL_NAME = 'plans';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.store';

    const MODEL_ATTRIBUTE_NAME = 'name';

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(PlansPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function guests_users_cannot_create_plans()
    {
        $plan = array_filter(Plan::factory()->raw());

        $response = $this->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withData([
                'type' => self::MODEL_PLURAL_NAME,
                'attributes' => $plan
            ])
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unauthorized (401)
        $response->assertStatus(401);

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $plan);
    }

    /** @test */
    public function authenticated_users_as_admin_can_create_plans()
    {
        $plan = array_filter(Plan::factory()->raw());

        $data = [
            'type'       => self::MODEL_PLURAL_NAME,
            'attributes' => $plan
        ];

        $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE))
            ->assertCreated();

        $this->assertDatabaseHas(self::MODEL_PLURAL_NAME, [
            self::MODEL_ATTRIBUTE_NAME => $plan[self::MODEL_ATTRIBUTE_NAME],
        ]);
    }

    /** @test */
    public function plan_name_is_required()
    {
        $plan = Plan::factory()->raw(['name' => '']);

        $data = [
            'type'       => self::MODEL_PLURAL_NAME,
            'attributes' => $plan
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

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $plan);
    }
}
