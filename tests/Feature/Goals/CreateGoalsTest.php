<?php

namespace Tests\Feature\Goals;

use App\Models\Goal;
use App\Models\User;
use Database\Seeders\permissionsSeeders\GoalsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CreateGoalsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'goals';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.store';

    const MODEL_ATTRIBUTE_NAME = 'name';
    const MODEL_ATTRIBUTE_DESCRIPTION = 'description';

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(GoalsPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function guests_users_cannot_create_goals()
    {
        $goal = array_filter(Goal::factory()->raw());

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $goal
        ];

        $response = $this->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unauthorized (401)
        $response->assertStatus(401);

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $goal);
    }

    /** @test */
    public function authenticated_users_as_admin_can_create_goals()
    {
        $goal = array_filter(Goal::factory()->raw());

        $data = [
            'type'       => self::MODEL_PLURAL_NAME,
            'attributes' => $goal
        ];

        $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE))
            ->assertCreated();

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                self::MODEL_ATTRIBUTE_NAME        => $goal[self::MODEL_ATTRIBUTE_NAME],
                self::MODEL_ATTRIBUTE_DESCRIPTION => $goal[self::MODEL_ATTRIBUTE_DESCRIPTION],
            ]
        );
    }

    /** @test */
    public function goal_name_is_required()
    {
        $goal = Goal::factory()->raw(
            [
                'name' => ''
            ]
        );

        $data = [
            'type'       => self::MODEL_PLURAL_NAME,
            'attributes' => $goal
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => '/data/attributes/name'],
                'detail' => 'The name field is required.'
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $goal);
    }

    /** @test */
    public function goal_name_must_be_unique()
    {
        $goal = Goal::factory()->create();

        $data = [
            'type'       => self::MODEL_PLURAL_NAME,
            'attributes' => array_filter(Goal::factory()->raw(
                [
                    'name' => $goal->name
                ]
            ))
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => '/data/attributes/name'],
                'detail' => 'The name has already been taken.'
            ]
        );

        $this->assertDatabaseCount(self::MODEL_PLURAL_NAME, 1);
    }

    /** @test */
    public function goal_description_is_required()
    {
        $goal = Goal::factory()->raw(
            [
                self::MODEL_ATTRIBUTE_DESCRIPTION => ''
            ]
        );

        $data = [
            'type'       => self::MODEL_PLURAL_NAME,
            'attributes' => $goal
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => '/data/attributes/description'],
                'detail' => 'The description field is required.'
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $goal);
    }

    /** @test */
    public function goal_description_must_be_a_string()
    {
        $goal = Goal::factory()->raw(
            [
                self::MODEL_ATTRIBUTE_DESCRIPTION => 123
            ]
        );

        $data = [
            'type'       => self::MODEL_PLURAL_NAME,
            'attributes' => $goal
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => '/data/attributes/description'],
                'detail' => 'The description field must be a string.'
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $goal);
    }
}
