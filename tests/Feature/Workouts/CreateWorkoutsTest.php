<?php

namespace Tests\Feature\Workouts;

use App\Enums\MusclePriorityEnum;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\Muscle;
use App\Models\User;
use App\Models\Workout;
use Database\Seeders\RoleSeeder;
use Database\Seeders\permissionsSeeders\WorkoutsPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CreateWorkoutsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'workouts';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.store';

    const BELONGS_TO_CATEGORY_RELATIONSHIP_SINGLE_NAME = 'category';
    const BELONGS_TO_CATEGORY_RELATIONSHIP_PLURAL_NAME = 'categories';

    const BELONGS_TO_MANY_MUSCLES_RELATIONSHIP_SINGLE_NAME = 'muscle';
    const BELONGS_TO_MANY_MUSCLES_RELATIONSHIP_PLURAL_NAME = 'muscles';

    const BELONGS_TO_MANY_EQUIPMENTS_RELATIONSHIP_SINGLE_NAME = 'equipment';
    const BELONGS_TO_MANY_EQUIPMENTS_RELATIONSHIP_PLURAL_NAME = 'equipments';

    const PIVOT_TABLE_MUSCLE_WORKOUT = 'muscle_workout';
    const PIVOT_TABLE_EQUIPMENT_WORKOUT = 'equipment_workout';

    const MODEL_ATTRIBUTE_NAME = 'name';
    const MODEL_ATTRIBUTE_PERFORMANCE = 'performance';
    const MODEL_ATTRIBUTE_COMMENTS = 'comments';
    const MODEL_ATTRIBUTE_CORRECTIONS = 'corrections';
    const MODEL_ATTRIBUTE_WARNINGS = 'warnings';
    const MODEL_ATTRIBUTE_IMAGE = 'image';
    const MODEL_ATTRIBUTE_LEVELS = 'levels';
    const MODEL_IMAGE_ROUTE_PATH = 'app/public/1/';
    const MODEL_LEVELS_ATTRIBUTE = '/data/attributes/levels';

    protected User $user;
    protected string $token;
    protected Category $category;

    // For making relationship test with 3 muscles
    protected Muscle $muscle1;
    protected Muscle $muscle2;
    protected Muscle $muscle3;

    // For making relationship test with 3 equipments
    protected Equipment $equipment1;
    protected Equipment $equipment2;
    protected Equipment $equipment3;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(WorkoutsPermissionsSeeder::class);
        }

        [$this->user, $this->token] = $this->createUserWithToken();

        $this->category = Category::factory()->create();

        // For making relationship test with 3 muscles
        $this->muscle1 = Muscle::factory()->create();
        $this->muscle2 = Muscle::factory()->create();
        $this->muscle3 = Muscle::factory()->create();

        // For making relationship test with 3 equipments
        $this->equipment1 = Equipment::factory()->create();
        $this->equipment2 = Equipment::factory()->create();
        $this->equipment3 = Equipment::factory()->create();

        Storage::disk('public')->deleteDirectory('.');
    }

    /** @test */
    public function guests_users_cannot_create_workouts()
    {
        $file = UploadedFile::fake()->image($fileName = Str::uuid()->toString() . '.jpg');

        $workout = array_filter(
            Workout::factory()->raw(
                [
                    self::MODEL_ATTRIBUTE_IMAGE => $file
                ]
            )
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $workout
        ];

        $response = $this->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unauthorized (401)
        $response->assertStatus(401);

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $workout);

        // Verify that the image was not saved
        $this->assertFileDoesNotExist(storage_path(self::MODEL_IMAGE_ROUTE_PATH . $fileName));
    }

    /** @test */
    public function authenticated_users_as_admin_can_create_workouts_including_category()
    {
        $file = UploadedFile::fake()->image($fileName = Str::uuid()->toString() . '.jpg');

        $workout = array_filter(
            Workout::factory()->raw(
                [
                    self::MODEL_ATTRIBUTE_IMAGE => $file
                ]
            )
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $workout);

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $workout,
            'relationships' => [
                self::BELONGS_TO_CATEGORY_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::BELONGS_TO_CATEGORY_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->category->getRouteKey()
                    ]
                ],
            ]
        ];

        $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::BELONGS_TO_CATEGORY_RELATIONSHIP_SINGLE_NAME)
            ->withHeader('Authorization', $this->token)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE))
            ->assertCreated();

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                'id' => Workout::whereName($workout[self::MODEL_ATTRIBUTE_NAME])->first()->getRouteKey(),
                'category_id' => $this->category->getRouteKey(),
                self::MODEL_ATTRIBUTE_NAME => $workout[self::MODEL_ATTRIBUTE_NAME],
                self::MODEL_ATTRIBUTE_PERFORMANCE => $workout[self::MODEL_ATTRIBUTE_PERFORMANCE],
                self::MODEL_ATTRIBUTE_COMMENTS => $workout[self::MODEL_ATTRIBUTE_COMMENTS],
                self::MODEL_ATTRIBUTE_CORRECTIONS => $workout[self::MODEL_ATTRIBUTE_CORRECTIONS],
                self::MODEL_ATTRIBUTE_WARNINGS => $workout[self::MODEL_ATTRIBUTE_WARNINGS],
            ]
        );

        $this->assertFileExists(storage_path(self::MODEL_IMAGE_ROUTE_PATH . $fileName));
    }

    /** @test */
    public function authenticated_users_as_admin_can_create_workouts_including_muscles()
    {
        $file = UploadedFile::fake()->image($fileName = Str::uuid()->toString() . '.jpg');

        $workout = array_filter(
            Workout::factory()->raw(
                [
                    self::MODEL_ATTRIBUTE_IMAGE => $file
                ]
            )
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $workout);

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $workout,
            'relationships' => [
                self::BELONGS_TO_CATEGORY_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::BELONGS_TO_CATEGORY_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->category->getRouteKey()
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
            ->includePaths(self::BELONGS_TO_CATEGORY_RELATIONSHIP_SINGLE_NAME)
            ->includePaths(self::BELONGS_TO_MANY_MUSCLES_RELATIONSHIP_PLURAL_NAME)
            ->withHeader('Authorization', $this->token)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE))
            ->assertCreated();

        $workoutId = Workout::whereName($workout[self::MODEL_ATTRIBUTE_NAME])->first()->getRouteKey();

        // Verify BelongsTo relationship with category model and Workout data
        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                'id' => $workoutId,
                'category_id' => $this->category->getRouteKey(),
                self::MODEL_ATTRIBUTE_NAME => $workout[self::MODEL_ATTRIBUTE_NAME],
                self::MODEL_ATTRIBUTE_PERFORMANCE => $workout[self::MODEL_ATTRIBUTE_PERFORMANCE],
                self::MODEL_ATTRIBUTE_COMMENTS => $workout[self::MODEL_ATTRIBUTE_COMMENTS],
                self::MODEL_ATTRIBUTE_CORRECTIONS => $workout[self::MODEL_ATTRIBUTE_CORRECTIONS],
                self::MODEL_ATTRIBUTE_WARNINGS => $workout[self::MODEL_ATTRIBUTE_WARNINGS],
            ]
        );

        // Verify BelongsToMany relationship with Muscle model
        $this->assertDatabaseHas(
            self::PIVOT_TABLE_MUSCLE_WORKOUT,
            [
                'muscle_id' => $this->muscle1->getRouteKey(),
                'workout_id' => $workoutId,
                'priority' => MusclePriorityEnum::PRINCIPAL
            ]
        );

        $this->assertFileExists(storage_path(self::MODEL_IMAGE_ROUTE_PATH . $fileName));
    }

    /** @test */
    public function authenticated_users_as_admin_can_create_workouts_including_3_muscles()
    {
        $file = UploadedFile::fake()->image($fileName = Str::uuid()->toString() . '.jpg');

        $workout = array_filter(
            Workout::factory()->raw(
                [
                    self::MODEL_ATTRIBUTE_IMAGE => $file
                ]
            )
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $workout);

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $workout,
            'relationships' => [
                self::BELONGS_TO_CATEGORY_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::BELONGS_TO_CATEGORY_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->category->getRouteKey()
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
            ->includePaths(self::BELONGS_TO_CATEGORY_RELATIONSHIP_SINGLE_NAME)
            ->includePaths(self::BELONGS_TO_MANY_MUSCLES_RELATIONSHIP_PLURAL_NAME)
            ->withHeader('Authorization', $this->token)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE))
            ->assertCreated();

        $workoutId = Workout::whereName($workout[self::MODEL_ATTRIBUTE_NAME])->first()->getRouteKey();

        // Verify BelongsTo relationship with category model and Workout data
        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                'id' => $workoutId,
                'category_id' => $this->category->getRouteKey(),
                self::MODEL_ATTRIBUTE_NAME => $workout[self::MODEL_ATTRIBUTE_NAME],
                self::MODEL_ATTRIBUTE_PERFORMANCE => $workout[self::MODEL_ATTRIBUTE_PERFORMANCE],
                self::MODEL_ATTRIBUTE_COMMENTS => $workout[self::MODEL_ATTRIBUTE_COMMENTS],
                self::MODEL_ATTRIBUTE_CORRECTIONS => $workout[self::MODEL_ATTRIBUTE_CORRECTIONS],
                self::MODEL_ATTRIBUTE_WARNINGS => $workout[self::MODEL_ATTRIBUTE_WARNINGS],
            ]
        );

        // Verify BelongsToMany relationship with Muscle model
        $this->assertDatabaseHas(
            self::PIVOT_TABLE_MUSCLE_WORKOUT,
            [
                'muscle_id' => $this->muscle1->getRouteKey(),
                'workout_id' => $workoutId,
                'priority' => MusclePriorityEnum::PRINCIPAL
            ]
        );

        $this->assertDatabaseHas(
            self::PIVOT_TABLE_MUSCLE_WORKOUT,
            [
                'muscle_id' => $this->muscle2->getRouteKey(),
                'workout_id' => $workoutId,
                'priority' => MusclePriorityEnum::SECONDARY
            ]
        );

        $this->assertDatabaseHas(
            self::PIVOT_TABLE_MUSCLE_WORKOUT,
            [
                'muscle_id' => $this->muscle3->getRouteKey(),
                'workout_id' => $workoutId,
                'priority' => MusclePriorityEnum::ANTAGONIST
            ]
        );

        $this->assertFileExists(storage_path(self::MODEL_IMAGE_ROUTE_PATH . $fileName));
    }

    /** @test */
    public function authenticated_users_as_admin_can_create_workouts_including_3_equipments()
    {
        $file = UploadedFile::fake()->image(Str::uuid()->toString() . '.jpg');

        $workout = array_filter(
            Workout::factory()->raw(
                [
                    self::MODEL_ATTRIBUTE_IMAGE => $file
                ]
            )
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $workout);

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $workout,
            'relationships' => [
                self::BELONGS_TO_CATEGORY_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::BELONGS_TO_CATEGORY_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->category->getRouteKey()
                    ]
                ],
                self::BELONGS_TO_MANY_EQUIPMENTS_RELATIONSHIP_PLURAL_NAME => [
                    'data' => [
                        [
                            'type' => self::BELONGS_TO_MANY_EQUIPMENTS_RELATIONSHIP_PLURAL_NAME,
                            'id' => (string) $this->equipment1->getRouteKey()
                        ],
                        [
                            'type' => self::BELONGS_TO_MANY_EQUIPMENTS_RELATIONSHIP_PLURAL_NAME,
                            'id' => (string) $this->equipment2->getRouteKey()
                        ],
                        [
                            'type' => self::BELONGS_TO_MANY_EQUIPMENTS_RELATIONSHIP_PLURAL_NAME,
                            'id' => (string) $this->equipment3->getRouteKey()
                        ]
                    ]
                ]
            ]
        ];

        $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::BELONGS_TO_CATEGORY_RELATIONSHIP_SINGLE_NAME)
            ->includePaths(self::BELONGS_TO_MANY_EQUIPMENTS_RELATIONSHIP_PLURAL_NAME)
            ->withHeader('Authorization', $this->token)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE))
            ->assertCreated();

        $workoutId = Workout::whereName($workout[self::MODEL_ATTRIBUTE_NAME])->first()->getRouteKey();

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                'id' => $workoutId,
                'category_id' => $this->category->getRouteKey(),
                self::MODEL_ATTRIBUTE_NAME => $workout[self::MODEL_ATTRIBUTE_NAME],
                self::MODEL_ATTRIBUTE_PERFORMANCE => $workout[self::MODEL_ATTRIBUTE_PERFORMANCE],
                self::MODEL_ATTRIBUTE_COMMENTS => $workout[self::MODEL_ATTRIBUTE_COMMENTS],
                self::MODEL_ATTRIBUTE_CORRECTIONS => $workout[self::MODEL_ATTRIBUTE_CORRECTIONS],
                self::MODEL_ATTRIBUTE_WARNINGS => $workout[self::MODEL_ATTRIBUTE_WARNINGS],
            ]
        );

        // Verify BelongsToMany relationship with Equipment model
        $this->assertDatabaseHas(
            self::PIVOT_TABLE_EQUIPMENT_WORKOUT,
            [
                'equipment_id' => $this->equipment1->getRouteKey(),
                'workout_id' => $workoutId
            ]
        );

        $this->assertDatabaseHas(
            self::PIVOT_TABLE_EQUIPMENT_WORKOUT,
            [
                'equipment_id' => $this->equipment2->getRouteKey(),
                'workout_id' => $workoutId
            ]
        );

        $this->assertDatabaseHas(
            self::PIVOT_TABLE_EQUIPMENT_WORKOUT,
            [
                'equipment_id' => $this->equipment3->getRouteKey(),
                'workout_id' => $workoutId
            ]
        );
    }

    /** @test */
    public function authenticated_users_as_user_cannot_create_workouts()
    {
        $file = UploadedFile::fake()->image($fileName = Str::uuid()->toString() . '.jpg');

        $workout = array_filter(
            Workout::factory()->raw(
                [
                    self::MODEL_ATTRIBUTE_IMAGE => $file
                ]
            )
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $workout,
            'relationships' => [
                self::BELONGS_TO_CATEGORY_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::BELONGS_TO_CATEGORY_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->category->getRouteKey()
                    ]
                ]
            ]
        ];

        $user = User::factory()->create()->assignRole('user');

        $response = $this->actingAs($user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::BELONGS_TO_CATEGORY_RELATIONSHIP_SINGLE_NAME)
            ->withHeader('Authorization', $this->token)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Forbidden (403)
        $response->assertStatus(403);

        $this->assertDatabaseMissing(
            self::MODEL_PLURAL_NAME,
            [
                self::MODEL_ATTRIBUTE_NAME => $workout[self::MODEL_ATTRIBUTE_NAME],
                self::MODEL_ATTRIBUTE_PERFORMANCE => $workout[self::MODEL_ATTRIBUTE_PERFORMANCE],
                self::MODEL_ATTRIBUTE_COMMENTS => $workout[self::MODEL_ATTRIBUTE_COMMENTS],
                self::MODEL_ATTRIBUTE_CORRECTIONS => $workout[self::MODEL_ATTRIBUTE_CORRECTIONS],
                self::MODEL_ATTRIBUTE_WARNINGS => $workout[self::MODEL_ATTRIBUTE_WARNINGS],
            ]
        );

        // Verify that the image was not saved
        $this->assertFileDoesNotExist(storage_path(self::MODEL_IMAGE_ROUTE_PATH . $fileName));
    }

    /** @test */
    public function workout_name_is_required()
    {
        $file = UploadedFile::fake()->image($fileName = Str::uuid()->toString() . '.jpg');

        $workout = array_filter(
            Workout::factory()->raw(
                [
                    self::MODEL_ATTRIBUTE_NAME => '',
                    self::MODEL_ATTRIBUTE_IMAGE => $file
                ],
            )
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $workout,
            'relationships' => [
                self::BELONGS_TO_CATEGORY_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::BELONGS_TO_CATEGORY_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->category->getRouteKey()
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::BELONGS_TO_CATEGORY_RELATIONSHIP_SINGLE_NAME)
            ->withHeader('Authorization', $this->token)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => '/data/attributes/name'],
                'detail' => 'The name field is required.'
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $workout);

        // Verify that the image was not saved
        $this->assertFileDoesNotExist(storage_path(self::MODEL_IMAGE_ROUTE_PATH . $fileName));
    }

    /** @test */
    public function workout_performance_is_required()
    {
        $file = UploadedFile::fake()->image($fileName = Str::uuid()->toString() . '.jpg');

        $workout = array_filter(
            Workout::factory()->raw(
                [
                    self::MODEL_ATTRIBUTE_PERFORMANCE => '',
                    self::MODEL_ATTRIBUTE_IMAGE => $file
                ]
            )
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $workout,
            'relationships' => [
                self::BELONGS_TO_CATEGORY_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::BELONGS_TO_CATEGORY_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->category->getRouteKey()
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::BELONGS_TO_CATEGORY_RELATIONSHIP_SINGLE_NAME)
            ->withHeader('Authorization', $this->token)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => '/data/attributes/performance'],
                'detail' => 'The performance field is required.'
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $workout);

        // Verify that the image was not saved
        $this->assertFileDoesNotExist(storage_path(self::MODEL_IMAGE_ROUTE_PATH . $fileName));
    }

    /** @test */
    public function workouts_can_be_created_only_with_name_and_performance()
    {
        $file = UploadedFile::fake()->image($fileName = Str::uuid()->toString() . '.jpg');

        $workout = array_filter(
            Workout::factory()->raw(
                [
                    self::MODEL_ATTRIBUTE_COMMENTS => '',
                    self::MODEL_ATTRIBUTE_CORRECTIONS => '',
                    self::MODEL_ATTRIBUTE_WARNINGS => '',
                    self::MODEL_ATTRIBUTE_IMAGE => $file
                ]
            )
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $workout);

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $workout,
            'relationships' => [
                self::BELONGS_TO_CATEGORY_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::BELONGS_TO_CATEGORY_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->category->getRouteKey()
                    ]
                ]
            ]
        ];

        $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::BELONGS_TO_CATEGORY_RELATIONSHIP_SINGLE_NAME)
            ->withHeader('Authorization', $this->token)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE))
            ->assertCreated();

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                self::MODEL_ATTRIBUTE_NAME => $workout[self::MODEL_ATTRIBUTE_NAME],
                self::MODEL_ATTRIBUTE_PERFORMANCE => $workout[self::MODEL_ATTRIBUTE_PERFORMANCE],
            ]
        );

        $this->assertFileExists(storage_path(self::MODEL_IMAGE_ROUTE_PATH . $fileName));
    }

    /** @test */
    public function workouts_can_be_created_with_levels()
    {
        $file = UploadedFile::fake()->image($fileName = Str::uuid()->toString() . '.jpg');

        $workout = array_filter(
            Workout::factory()->raw(
                [
                    self::MODEL_ATTRIBUTE_LEVELS => json_encode(['beginner', 'intermediate']),
                    self::MODEL_ATTRIBUTE_IMAGE => $file
                ]
            )
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $workout,
            'relationships' => [
                self::BELONGS_TO_CATEGORY_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::BELONGS_TO_CATEGORY_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->category->getRouteKey()
                    ]
                ]
            ]
        ];

        $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::BELONGS_TO_CATEGORY_RELATIONSHIP_SINGLE_NAME)
            ->withHeader('Authorization', $this->token)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE))
            ->assertCreated();

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                self::MODEL_ATTRIBUTE_LEVELS => json_encode(['beginner', 'intermediate']),
            ]
        );

        $this->assertFileExists(storage_path(self::MODEL_IMAGE_ROUTE_PATH . $fileName));
    }

    /** @test */
    public function workouts_cannot_be_created_with_invalid_levels()
    {
        $file = UploadedFile::fake()->image($fileName = Str::uuid()->toString() . '.jpg');

        $workout = array_filter(
            Workout::factory()->raw(
                [
                    self::MODEL_ATTRIBUTE_LEVELS => json_encode(['invalid_level']),
                    self::MODEL_ATTRIBUTE_IMAGE => $file
                ]
            )
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $workout,
            'relationships' => [
                self::BELONGS_TO_CATEGORY_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::BELONGS_TO_CATEGORY_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->category->getRouteKey()
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::BELONGS_TO_CATEGORY_RELATIONSHIP_SINGLE_NAME)
            ->withHeader('Authorization', $this->token)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => self::MODEL_LEVELS_ATTRIBUTE],
                'detail' => __('validation.allowed_values', [
                    'attribute' => 'levels',
                    'values' => 'beginner, intermediate, advanced'
                ])
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $workout);

        // Verify that the image was not saved
        $this->assertFileDoesNotExist(storage_path(self::MODEL_IMAGE_ROUTE_PATH . $fileName));
    }

    /** @test */
    public function workouts_cannot_be_created_with_invalid_json_levels()
    {
        $file = UploadedFile::fake()->image($fileName = Str::uuid()->toString() . '.jpg');

        $workout = array_filter(
            Workout::factory()->raw(
                [
                    self::MODEL_ATTRIBUTE_LEVELS => 'invalid_json',
                    self::MODEL_ATTRIBUTE_IMAGE => $file
                ]
            )
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $workout,
            'relationships' => [
                self::BELONGS_TO_CATEGORY_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::BELONGS_TO_CATEGORY_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->category->getRouteKey()
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::BELONGS_TO_CATEGORY_RELATIONSHIP_SINGLE_NAME)
            ->withHeader('Authorization', $this->token)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => self::MODEL_LEVELS_ATTRIBUTE],
                'detail' => __('validation.json_decode_error', ['attribute' => 'levels'])
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $workout);

        // Verify that the image was not saved
        $this->assertFileDoesNotExist(storage_path(self::MODEL_IMAGE_ROUTE_PATH . $fileName));
    }

    /** @test */
    public function workouts_cannot_be_created_with_invalid_array_levels()
    {
        $file = UploadedFile::fake()->image($fileName = Str::uuid()->toString() . '.jpg');

        $workout = array_filter(
            Workout::factory()->raw(
                [
                    self::MODEL_ATTRIBUTE_LEVELS => json_encode('invalid_array'),
                    self::MODEL_ATTRIBUTE_IMAGE => $file
                ]
            )
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $workout,
            'relationships' => [
                self::BELONGS_TO_CATEGORY_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::BELONGS_TO_CATEGORY_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->category->getRouteKey()
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::BELONGS_TO_CATEGORY_RELATIONSHIP_SINGLE_NAME)
            ->withHeader('Authorization', $this->token)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => self::MODEL_LEVELS_ATTRIBUTE],
                'detail' => __('validation.array', [
                    'attribute' => 'levels',
                ])
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $workout);

        // Verify that the image was not saved
        $this->assertFileDoesNotExist(storage_path(self::MODEL_IMAGE_ROUTE_PATH . $fileName));
    }

    /** @test */
    public function workouts_cannot_be_created_with_duplicated_levels()
    {
        $file = UploadedFile::fake()->image($fileName = Str::uuid()->toString() . '.jpg');

        $workout = array_filter(
            Workout::factory()->raw(
                [
                    self::MODEL_ATTRIBUTE_LEVELS => ['beginner', 'intermediate', 'beginner'],
                    self::MODEL_ATTRIBUTE_IMAGE => $file
                ]
            )
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $workout,
            'relationships' => [
                self::BELONGS_TO_CATEGORY_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::BELONGS_TO_CATEGORY_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->category->getRouteKey()
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::BELONGS_TO_CATEGORY_RELATIONSHIP_SINGLE_NAME)
            ->withHeader('Authorization', $this->token)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => self::MODEL_LEVELS_ATTRIBUTE],
                'detail' => __('validation.duplicated', ['attribute' => 'levels'])
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $workout);

        // Verify that the image was not saved
        $this->assertFileDoesNotExist(storage_path(self::MODEL_IMAGE_ROUTE_PATH . $fileName));
    }
}
