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

class CreateWorkoutsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'workouts';
    const MODEL_INCLUDE_RELATIONSHIP_SINGLE_NAME = 'subcategory';
    const MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME = 'subcategories';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.store';

    const MODEL_ATTRIBUTE_NAME = 'name';
    const MODEL_ATTRIBUTE_PERFORMANCE = 'performance';
    const MODEL_ATTRIBUTE_COMMENTS = 'comments';
    const MODEL_ATTRIBUTE_CORRECTIONS = 'corrections';
    const MODEL_ATTRIBUTE_WARNINGS = 'warnings';

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
    public function guests_users_cannot_create_workouts()
    {
        $workout = array_filter(Workout::factory()->raw());

        $response = $this->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withData([
                'type' => self::MODEL_PLURAL_NAME,
                'attributes' => $workout
            ])
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unauthorized (401)
        $response->assertStatus(401);

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $workout);
    }

    /** @test */
    public function authenticated_users_as_admin_can_create_workouts()
    {
        $workout = array_filter(Workout::factory()->raw());

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $workout);

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $workout,
            'relationships' => [
                self::MODEL_INCLUDE_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->subcategory->getRouteKey()
                    ]
                ]
            ]
        ];

        $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withData($data)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_SINGLE_NAME)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE))
            ->assertCreated();

        $this->assertDatabaseHas(self::MODEL_PLURAL_NAME, [
            self::MODEL_ATTRIBUTE_NAME        => $workout[self::MODEL_ATTRIBUTE_NAME],
            self::MODEL_ATTRIBUTE_PERFORMANCE => $workout[self::MODEL_ATTRIBUTE_PERFORMANCE],
            self::MODEL_ATTRIBUTE_COMMENTS    => $workout[self::MODEL_ATTRIBUTE_COMMENTS],
            self::MODEL_ATTRIBUTE_CORRECTIONS => $workout[self::MODEL_ATTRIBUTE_CORRECTIONS],
            self::MODEL_ATTRIBUTE_WARNINGS    => $workout[self::MODEL_ATTRIBUTE_WARNINGS],
        ]);
    }

    /** @test */
    public function authenticated_users_as_user_cannot_create_workouts()
    {
        $workout = array_filter(Workout::factory()->raw());

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $workout,
            'relationships' => [
                self::MODEL_INCLUDE_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->subcategory->getRouteKey()
                    ]
                ]
            ]
        ];

        $user = User::factory()->create()->assignRole('user');

        $response = $this->actingAs($user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_SINGLE_NAME)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Forbidden (403)
        $response->assertStatus(403);

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, [
            self::MODEL_ATTRIBUTE_NAME        => $workout[self::MODEL_ATTRIBUTE_NAME],
            self::MODEL_ATTRIBUTE_PERFORMANCE => $workout[self::MODEL_ATTRIBUTE_PERFORMANCE],
            self::MODEL_ATTRIBUTE_COMMENTS    => $workout[self::MODEL_ATTRIBUTE_COMMENTS],
            self::MODEL_ATTRIBUTE_CORRECTIONS => $workout[self::MODEL_ATTRIBUTE_CORRECTIONS],
            self::MODEL_ATTRIBUTE_WARNINGS    => $workout[self::MODEL_ATTRIBUTE_WARNINGS],
        ]);
    }

    /** @test */
    public function workout_name_is_required()
    {
        $workout = array_filter(Workout::factory()->raw(
            [
                self::MODEL_ATTRIBUTE_NAME => ''
            ]
        ));

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $workout,
            'relationships' => [
                self::MODEL_INCLUDE_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->subcategory->getRouteKey()
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_SINGLE_NAME)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(422, [
            'source' => ['pointer' => '/data/attributes/name'],
            'detail' => 'The name field is required.'
        ]);

        $response->assertSee('data\/attributes\/name');

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $workout);
    }

    /** @test */
    public function workout_performance_is_required()
    {
        $workout = array_filter(Workout::factory()->raw(
            [
                self::MODEL_ATTRIBUTE_PERFORMANCE => ''
            ]
        ));

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $workout,
            'relationships' => [
                self::MODEL_INCLUDE_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->subcategory->getRouteKey()
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_SINGLE_NAME)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(422, [
            'source' => ['pointer' => '/data/attributes/performance'],
            'detail' => 'The performance field is required.'
        ]);

        $response->assertSee('data\/attributes\/performance');

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $workout);
    }

    /** @test */
    public function workouts_can_be_created_only_with_name_and_performance()
    {
        $workout = array_filter(Workout::factory()->raw(
            [
                self::MODEL_ATTRIBUTE_COMMENTS    => '',
                self::MODEL_ATTRIBUTE_CORRECTIONS => '',
                self::MODEL_ATTRIBUTE_WARNINGS    => '',
            ]
        ));

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $workout);

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $workout,
            'relationships' => [
                self::MODEL_INCLUDE_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->subcategory->getRouteKey()
                    ]
                ]
            ]
        ];

        $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withData($data)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_SINGLE_NAME)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE))
            ->assertCreated();

        $this->assertDatabaseHas(self::MODEL_PLURAL_NAME, [
            self::MODEL_ATTRIBUTE_NAME        => $workout[self::MODEL_ATTRIBUTE_NAME],
            self::MODEL_ATTRIBUTE_PERFORMANCE => $workout[self::MODEL_ATTRIBUTE_PERFORMANCE],
        ]);
    }
}
