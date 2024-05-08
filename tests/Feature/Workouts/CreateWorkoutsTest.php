<?php

namespace Tests\Feature\Workouts;

use App\Enums\MusclePriorityEnum;
use App\Models\Muscle;
use App\Models\Subcategory;
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
    const MODEL_ATTRIBUTE_IMAGE = 'image';
    const MODEL_IMAGE_ROUTE_PATH = 'app/public/1/';

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

        Storage::disk('public')->deleteDirectory('.');
    }

    /** @test */
    public function guests_users_cannot_create_workouts()
    {
        $file = UploadedFile::fake()->image($fileName = Str::uuid()->toString() . '.jpg');

        $workout = array_filter(Workout::factory()->raw(
            [
                self::MODEL_ATTRIBUTE_IMAGE => $file
            ]
        ));

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
    public function authenticated_users_as_admin_can_create_workouts_including_subcategory()
    {
        $file = UploadedFile::fake()->image($fileName = Str::uuid()->toString() . '.jpg');

        $workout = array_filter(Workout::factory()->raw(
            [
                self::MODEL_ATTRIBUTE_IMAGE => $file
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
                ],
            ]
        ];

        $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::BELONGS_TO_SUBCATEGORY_RELATIONSHIP_SINGLE_NAME)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE))
            ->assertCreated();

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                'id'             => Workout::whereName($workout[self::MODEL_ATTRIBUTE_NAME])->first()->getRouteKey(),
                'subcategory_id' => $this->subcategory->getRouteKey(),
                self::MODEL_ATTRIBUTE_NAME        => $workout[self::MODEL_ATTRIBUTE_NAME],
                self::MODEL_ATTRIBUTE_PERFORMANCE => $workout[self::MODEL_ATTRIBUTE_PERFORMANCE],
                self::MODEL_ATTRIBUTE_COMMENTS    => $workout[self::MODEL_ATTRIBUTE_COMMENTS],
                self::MODEL_ATTRIBUTE_CORRECTIONS => $workout[self::MODEL_ATTRIBUTE_CORRECTIONS],
                self::MODEL_ATTRIBUTE_WARNINGS    => $workout[self::MODEL_ATTRIBUTE_WARNINGS],
            ]
        );

        $this->assertFileExists(storage_path(self::MODEL_IMAGE_ROUTE_PATH . $fileName));
    }

    /** @test */
    public function authenticated_users_as_admin_can_create_workouts_including_muscles()
    {
        $file = UploadedFile::fake()->image($fileName = Str::uuid()->toString() . '.jpg');

        $workout = array_filter(Workout::factory()->raw(
            [
                self::MODEL_ATTRIBUTE_IMAGE => $file
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
        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                'id'             => $workoutId,
                'subcategory_id' => $this->subcategory->getRouteKey(),
                self::MODEL_ATTRIBUTE_NAME        => $workout[self::MODEL_ATTRIBUTE_NAME],
                self::MODEL_ATTRIBUTE_PERFORMANCE => $workout[self::MODEL_ATTRIBUTE_PERFORMANCE],
                self::MODEL_ATTRIBUTE_COMMENTS    => $workout[self::MODEL_ATTRIBUTE_COMMENTS],
                self::MODEL_ATTRIBUTE_CORRECTIONS => $workout[self::MODEL_ATTRIBUTE_CORRECTIONS],
                self::MODEL_ATTRIBUTE_WARNINGS    => $workout[self::MODEL_ATTRIBUTE_WARNINGS],
            ]
        );

        // Verify BelongsToMany relationship with Muscle model
        $this->assertDatabaseHas(
            self::PIVOT_TABLE_MUSCLE_WORKOUT,
            [
                'muscle_id'  => $this->muscle1->getRouteKey(),
                'workout_id' => $workoutId,
                'priority'   => MusclePriorityEnum::PRINCIPAL
            ]
        );

        $this->assertFileExists(storage_path(self::MODEL_IMAGE_ROUTE_PATH . $fileName));
    }

    /** @test */
    public function authenticated_users_as_admin_can_create_workouts_including_3_muscles()
    {
        $file = UploadedFile::fake()->image($fileName = Str::uuid()->toString() . '.jpg');

        $workout = array_filter(Workout::factory()->raw(
            [
                self::MODEL_ATTRIBUTE_IMAGE => $file
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
        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                'id'             => $workoutId,
                'subcategory_id' => $this->subcategory->getRouteKey(),
                self::MODEL_ATTRIBUTE_NAME        => $workout[self::MODEL_ATTRIBUTE_NAME],
                self::MODEL_ATTRIBUTE_PERFORMANCE => $workout[self::MODEL_ATTRIBUTE_PERFORMANCE],
                self::MODEL_ATTRIBUTE_COMMENTS    => $workout[self::MODEL_ATTRIBUTE_COMMENTS],
                self::MODEL_ATTRIBUTE_CORRECTIONS => $workout[self::MODEL_ATTRIBUTE_CORRECTIONS],
                self::MODEL_ATTRIBUTE_WARNINGS    => $workout[self::MODEL_ATTRIBUTE_WARNINGS],
            ]
        );

        // Verify BelongsToMany relationship with Muscle model
        $this->assertDatabaseHas(
            self::PIVOT_TABLE_MUSCLE_WORKOUT,
            [
                'muscle_id'  => $this->muscle1->getRouteKey(),
                'workout_id' => $workoutId,
                'priority'   => MusclePriorityEnum::PRINCIPAL
            ]
        );

        $this->assertDatabaseHas(
            self::PIVOT_TABLE_MUSCLE_WORKOUT,
            [
                'muscle_id'  => $this->muscle2->getRouteKey(),
                'workout_id' => $workoutId,
                'priority'   => MusclePriorityEnum::SECONDARY
            ]
        );

        $this->assertDatabaseHas(
            self::PIVOT_TABLE_MUSCLE_WORKOUT,
            [
                'muscle_id'  => $this->muscle3->getRouteKey(),
                'workout_id' => $workoutId,
                'priority'   => MusclePriorityEnum::ANTAGONIST
            ]
        );

        $this->assertFileExists(storage_path(self::MODEL_IMAGE_ROUTE_PATH . $fileName));
    }

    /** @test */
    public function authenticated_users_as_user_cannot_create_workouts()
    {
        $file = UploadedFile::fake()->image($fileName = Str::uuid()->toString() . '.jpg');

        $workout = array_filter(Workout::factory()->raw(
            [
                self::MODEL_ATTRIBUTE_IMAGE => $file
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

        $user = User::factory()->create()->assignRole('user');

        $response = $this->actingAs($user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::BELONGS_TO_SUBCATEGORY_RELATIONSHIP_SINGLE_NAME)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Forbidden (403)
        $response->assertStatus(403);

        $this->assertDatabaseMissing(
            self::MODEL_PLURAL_NAME,
            [
                self::MODEL_ATTRIBUTE_NAME        => $workout[self::MODEL_ATTRIBUTE_NAME],
                self::MODEL_ATTRIBUTE_PERFORMANCE => $workout[self::MODEL_ATTRIBUTE_PERFORMANCE],
                self::MODEL_ATTRIBUTE_COMMENTS    => $workout[self::MODEL_ATTRIBUTE_COMMENTS],
                self::MODEL_ATTRIBUTE_CORRECTIONS => $workout[self::MODEL_ATTRIBUTE_CORRECTIONS],
                self::MODEL_ATTRIBUTE_WARNINGS    => $workout[self::MODEL_ATTRIBUTE_WARNINGS],
            ]
        );

        // Verify that the image was not saved
        $this->assertFileDoesNotExist(storage_path(self::MODEL_IMAGE_ROUTE_PATH . $fileName));
    }

    /** @test */
    public function workout_name_is_required()
    {
        $file = UploadedFile::fake()->image($fileName = Str::uuid()->toString() . '.jpg');

        $workout = array_filter(Workout::factory()->raw(
            [
                self::MODEL_ATTRIBUTE_NAME => '',
                self::MODEL_ATTRIBUTE_IMAGE => $file
            ],
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

        $workout = array_filter(Workout::factory()->raw(
            [
                self::MODEL_ATTRIBUTE_PERFORMANCE => '',
                self::MODEL_ATTRIBUTE_IMAGE => $file
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

        $workout = array_filter(Workout::factory()->raw(
            [
                self::MODEL_ATTRIBUTE_COMMENTS    => '',
                self::MODEL_ATTRIBUTE_CORRECTIONS => '',
                self::MODEL_ATTRIBUTE_WARNINGS    => '',
                self::MODEL_ATTRIBUTE_IMAGE       => $file
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
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::BELONGS_TO_SUBCATEGORY_RELATIONSHIP_SINGLE_NAME)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE))->dump()
            ->assertCreated();

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                self::MODEL_ATTRIBUTE_NAME        => $workout[self::MODEL_ATTRIBUTE_NAME],
                self::MODEL_ATTRIBUTE_PERFORMANCE => $workout[self::MODEL_ATTRIBUTE_PERFORMANCE],
            ]
        );

        $this->assertFileExists(storage_path(self::MODEL_IMAGE_ROUTE_PATH . $fileName));
    }
}
