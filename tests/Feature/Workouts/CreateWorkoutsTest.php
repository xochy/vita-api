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

    // For making relationship test with 3 muscles
    protected Muscle $muscle1;
    protected Muscle $muscle2;
    protected Muscle $muscle3;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(WorkoutsPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
        $this->subcategory = Subcategory::factory()->forCategory()->create();

        // For making relationship test with 3 muscles
        $this->muscle1 = Muscle::factory()->create();
        $this->muscle2 = Muscle::factory()->create();
        $this->muscle3 = Muscle::factory()->create();
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
    public function authenticated_users_as_admin_can_create_workouts_including_subcategory()
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
            ]
        ];

        $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::BELONGS_TO_SUBCATEGORY_RELATIONSHIP_SINGLE_NAME)
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
    }

    /** @test */
    public function authenticated_users_as_admin_can_create_workouts_including_muscles()
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
                            'id' => (string) $this->muscle1->getRouteKey(),
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

        $workoutId = Workout::whereName($workout[self::MODEL_ATTRIBUTE_NAME])->first()->getRouteKey();

        // Verify BelongsTo relationship with Subcategory model and Workout data
        $this->assertDatabaseHas(self::MODEL_PLURAL_NAME, [
            'id'             => $workoutId,
            'subcategory_id' => $this->subcategory->getRouteKey(),
            self::MODEL_ATTRIBUTE_NAME        => $workout[self::MODEL_ATTRIBUTE_NAME],
            self::MODEL_ATTRIBUTE_PERFORMANCE => $workout[self::MODEL_ATTRIBUTE_PERFORMANCE],
            self::MODEL_ATTRIBUTE_COMMENTS    => $workout[self::MODEL_ATTRIBUTE_COMMENTS],
            self::MODEL_ATTRIBUTE_CORRECTIONS => $workout[self::MODEL_ATTRIBUTE_CORRECTIONS],
            self::MODEL_ATTRIBUTE_WARNINGS    => $workout[self::MODEL_ATTRIBUTE_WARNINGS],
        ]);

        // Verify BelongsToMany relationship with Muscle model
        $this->assertDatabaseHas(self::PIVOT_TABLE_MUSCLE_WORKOUT, [
            'muscle_id'  => $this->muscle1->getRouteKey(),
            'workout_id' => $workoutId,
            'priority'   => MusclePriorityEnum::PRINCIPAL
        ]);
    }

    /** @test */
    public function authenticated_users_as_admin_can_create_workouts_including_3_muscles()
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
                            'id' => (string) $this->muscle1->getRouteKey(),
                            'meta' => [
                                'pivot' => [
                                    'priority' => MusclePriorityEnum::PRINCIPAL
                                ]
                            ]
                        ],
                        [
                            'type' => self::BELONGS_TO_MANY_MUSCLES_RELATIONSHIP_PLURAL_NAME,
                            'id' => (string) $this->muscle2->getRouteKey(),
                            'meta' => [
                                'pivot' => [
                                    'priority' => MusclePriorityEnum::SECONDARY
                                ]
                            ]
                        ],
                        [
                            'type' => self::BELONGS_TO_MANY_MUSCLES_RELATIONSHIP_PLURAL_NAME,
                            'id' => (string) $this->muscle3->getRouteKey(),
                            'meta' => [
                                'pivot' => [
                                    'priority' => MusclePriorityEnum::ANTAGONIST
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

        $workoutId = Workout::whereName($workout[self::MODEL_ATTRIBUTE_NAME])->first()->getRouteKey();

        // Verify BelongsTo relationship with Subcategory model and Workout data
        $this->assertDatabaseHas(self::MODEL_PLURAL_NAME, [
            'id'             => $workoutId,
            'subcategory_id' => $this->subcategory->getRouteKey(),
            self::MODEL_ATTRIBUTE_NAME        => $workout[self::MODEL_ATTRIBUTE_NAME],
            self::MODEL_ATTRIBUTE_PERFORMANCE => $workout[self::MODEL_ATTRIBUTE_PERFORMANCE],
            self::MODEL_ATTRIBUTE_COMMENTS    => $workout[self::MODEL_ATTRIBUTE_COMMENTS],
            self::MODEL_ATTRIBUTE_CORRECTIONS => $workout[self::MODEL_ATTRIBUTE_CORRECTIONS],
            self::MODEL_ATTRIBUTE_WARNINGS    => $workout[self::MODEL_ATTRIBUTE_WARNINGS],
        ]);

        // Verify BelongsToMany relationship with Muscle model
        $this->assertDatabaseHas(self::PIVOT_TABLE_MUSCLE_WORKOUT, [
            'muscle_id'  => $this->muscle1->getRouteKey(),
            'workout_id' => $workoutId,
            'priority'   => MusclePriorityEnum::PRINCIPAL
        ]);

        $this->assertDatabaseHas(self::PIVOT_TABLE_MUSCLE_WORKOUT, [
            'muscle_id'  => $this->muscle2->getRouteKey(),
            'workout_id' => $workoutId,
            'priority'   => MusclePriorityEnum::SECONDARY
        ]);

        $this->assertDatabaseHas(self::PIVOT_TABLE_MUSCLE_WORKOUT, [
            'muscle_id'  => $this->muscle3->getRouteKey(),
            'workout_id' => $workoutId,
            'priority'   => MusclePriorityEnum::ANTAGONIST
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
