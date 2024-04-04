<?php

namespace Tests\Feature\Workouts;

use App\Enums\MusclePriorityEnum;
use App\Models\Muscle;
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
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.store';

    const BELONGS_TO_SUBCATEGORY_RELATIONSHIP_SINGLE_NAME = 'subcategory';
    const BELONGS_TO_SUBCATEGORY_RELATIONSHIP_PLURAL_NAME = 'subcategories';

    const BELONGS_TO_MANY_MUSCLES_RELATIONSHIP_SINGLE_NAME = 'muscle';
    const BELONGS_TO_MANY_MUSCLES_RELATIONSHIP_PLURAL_NAME = 'muscles';

    const PIVOT_TABLE_MUSCLE_WORKOUT = 'muscle_workout';

    const MODEL_ATTRIBUTE_NAME = 'name';
    const MODEL_ATTRIBUTE_PERFORMANCE = 'performance';
    const MODEL_ATTRIBUTE_COMMENTS = 'comments';
    const MODEL_ATTRIBUTE_CORRECTIONS = 'corrections';
    const MODEL_ATTRIBUTE_WARNINGS = 'warnings';

    protected User $user;
    protected Subcategory $subcategory;
    protected Muscle $muscle;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(WorkoutsPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
        $this->subcategory = Subcategory::factory()->forCategory()->create();
        $this->muscle = Muscle::factory()->create();
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
                self::BELONGS_TO_SUBCATEGORY_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::BELONGS_TO_SUBCATEGORY_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->subcategory->getRouteKey()
                    ]
                ],
                self::BELONGS_TO_MANY_MUSCLES_RELATIONSHIP_PLURAL_NAME => [
                    'data' => [
                        [
                            'type' => self::BELONGS_TO_MANY_MUSCLES_RELATIONSHIP_PLURAL_NAME,
                            'id' => (string) $this->muscle->getRouteKey(),
                            'meta' => [
                                'pivot' => [
                                    'priority' => MusclePriorityEnum::PRINCIPAL
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::BELONGS_TO_SUBCATEGORY_RELATIONSHIP_SINGLE_NAME)
            ->includePaths(self::BELONGS_TO_MANY_MUSCLES_RELATIONSHIP_PLURAL_NAME)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE))
            ->assertCreated();

        $this->assertDatabaseHas(self::MODEL_PLURAL_NAME, [
            'id'             => Workout::whereName($workout[self::MODEL_ATTRIBUTE_NAME])->first()->getRouteKey(),
            'subcategory_id' => $this->subcategory->getRouteKey(),
            self::MODEL_ATTRIBUTE_NAME        => $workout[self::MODEL_ATTRIBUTE_NAME],
            self::MODEL_ATTRIBUTE_PERFORMANCE => $workout[self::MODEL_ATTRIBUTE_PERFORMANCE],
            self::MODEL_ATTRIBUTE_COMMENTS    => $workout[self::MODEL_ATTRIBUTE_COMMENTS],
            self::MODEL_ATTRIBUTE_CORRECTIONS => $workout[self::MODEL_ATTRIBUTE_CORRECTIONS],
            self::MODEL_ATTRIBUTE_WARNINGS    => $workout[self::MODEL_ATTRIBUTE_WARNINGS],
        ]);

        // Verificación de la relación belongsToMany con músculos
        $this->assertDatabaseHas(self::PIVOT_TABLE_MUSCLE_WORKOUT, [
            'muscle_id'  => $this->muscle->getRouteKey(),
            'workout_id' => Workout::whereName($workout[self::MODEL_ATTRIBUTE_NAME])->first()->getRouteKey(),
            'priority'   => MusclePriorityEnum::PRINCIPAL
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
                self::BELONGS_TO_SUBCATEGORY_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::BELONGS_TO_SUBCATEGORY_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->subcategory->getRouteKey()
                    ]
                ]
            ]
        ];

        $user = User::factory()->create()->assignRole('user');

        $response = $this->actingAs($user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::BELONGS_TO_SUBCATEGORY_RELATIONSHIP_SINGLE_NAME)
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
                self::BELONGS_TO_SUBCATEGORY_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::BELONGS_TO_SUBCATEGORY_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->subcategory->getRouteKey()
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::BELONGS_TO_SUBCATEGORY_RELATIONSHIP_SINGLE_NAME)
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
                self::BELONGS_TO_SUBCATEGORY_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::BELONGS_TO_SUBCATEGORY_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->subcategory->getRouteKey()
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::BELONGS_TO_SUBCATEGORY_RELATIONSHIP_SINGLE_NAME)
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
                self::BELONGS_TO_SUBCATEGORY_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::BELONGS_TO_SUBCATEGORY_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->subcategory->getRouteKey()
                    ]
                ]
            ]
        ];

        $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withData($data)
            ->includePaths(self::BELONGS_TO_SUBCATEGORY_RELATIONSHIP_SINGLE_NAME)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE))
            ->assertCreated();

        $this->assertDatabaseHas(self::MODEL_PLURAL_NAME, [
            self::MODEL_ATTRIBUTE_NAME        => $workout[self::MODEL_ATTRIBUTE_NAME],
            self::MODEL_ATTRIBUTE_PERFORMANCE => $workout[self::MODEL_ATTRIBUTE_PERFORMANCE],
        ]);
    }
}
