<?php

namespace Tests\Feature\Routines;

use App\Models\Routine;
use App\Models\User;
use Database\Seeders\permissionsSeeders\RoutinesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CreateRoutinesTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'routines';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.store';

    const BELONGS_TO_MANY_WORKOUTS_RELATIONSHIP_SINGLE_NAME = 'workout';
    const BELONGS_TO_MANY_WORKOUTS_RELATIONSHIP_PLURAL_NAME = 'workouts';

    const PIVOT_TABLE_ROUTINE_WORKOUT = 'routine_workout';

    const MODEL_ATTRIBUTE_NAME = 'name';

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
    public function guests_users_cannot_create_routines()
    {
        $routine = array_filter(Routine::factory()->raw());

        $response = $this->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withData([
                'type' => self::MODEL_PLURAL_NAME,
                'attributes' => $routine
            ])
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unauthorized (401)
        $response->assertStatus(401);

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $routine);
    }

    /** @test */
    public function authenticated_users_can_create_workouts()
    {
        $routine = array_filter(Routine::factory()->raw());

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $routine);

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $routine
        ];

        $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE))
            ->assertCreated();

        $this->assertDatabaseHas(self::MODEL_PLURAL_NAME, $routine);
    }

    /** @test */
    public function authenticated_users_can_create_routines_with_related_workouts()
    {

    }
}
