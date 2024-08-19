<?php

namespace Tests\Feature\Workouts;

use App\Enums\MusclePriorityEnum;
use App\Models\Category;
use App\Models\Muscle;
use App\Models\User;
use App\Models\Workout;
use Database\Seeders\RoleSeeder;
use Database\Seeders\permissionsSeeders\WorkoutsPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UpdateWorkoutsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'workouts';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.update';

    const MODEL_ATTRIBUTE_NAME = 'name';
    const MODEL_ATTRIBUTE_PERFORMANCE = 'performance';
    const MODEL_ATTRIBUTE_COMMENTS = 'comments';
    const MODEL_ATTRIBUTE_CORRECTIONS = 'corrections';
    const MODEL_ATTRIBUTE_WARNINGS = 'warnings';
    const MODEL_ATTRIBUTE_IMAGE = 'image';
    const MODEL_ATTRIBUTE_IMAGE_NAME = 'Test.jpg';
    const MODEL_IMAGE_ROUTE_PATH = 'app/public/1/';

    const MODEL_NAME_ATTRIBUTE_VALUE = 'name changed';
    const MODEL_PERFORMANCE_ATTRIBUTE_VALUE = 'performance changed';
    const MODEL_COMMENTS_ATTRIBUTE_VALUE = 'comments changed';
    const MODEL_CORRECTIONS_ATTRIBUTE_VALUE = 'corrections changed';
    const MODEL_WARNINGS_ATTRIBUTE_VALUE = 'warnings changed';

    protected User $user;
    protected UploadedFile $file;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(WorkoutsPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');

        Storage::disk('public')->deleteDirectory('.');

        $this->file = UploadedFile::fake()->image(self::MODEL_ATTRIBUTE_IMAGE_NAME);
    }

    /** @test */
    public function guests_users_cannot_update_workouts()
    {
        $workout = Workout::factory()->forCategory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $workout->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME        => self::MODEL_NAME_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_PERFORMANCE => self::MODEL_PERFORMANCE_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_COMMENTS    => self::MODEL_COMMENTS_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_CORRECTIONS => self::MODEL_CORRECTIONS_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_WARNINGS    => self::MODEL_WARNINGS_ATTRIBUTE_VALUE,
            ]
        ];

        $response = $this->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $workout->getRouteKey()));

        // Unauthorized (401)
        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_users_as_admin_can_update_workouts()
    {
        $workout = Workout::factory()->forCategory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $workout->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME        => self::MODEL_NAME_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_PERFORMANCE => self::MODEL_PERFORMANCE_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_COMMENTS    => self::MODEL_COMMENTS_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_CORRECTIONS => self::MODEL_CORRECTIONS_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_WARNINGS    => self::MODEL_WARNINGS_ATTRIBUTE_VALUE,
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $workout->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                'id' => $workout->getRouteKey(),
                self::MODEL_ATTRIBUTE_NAME        => self::MODEL_NAME_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_PERFORMANCE => self::MODEL_PERFORMANCE_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_COMMENTS    => self::MODEL_COMMENTS_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_CORRECTIONS => self::MODEL_CORRECTIONS_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_WARNINGS    => self::MODEL_WARNINGS_ATTRIBUTE_VALUE,
            ]
        );
    }

    /** @test */
    public function can_update_the_workout_name_only()
    {
        $workout = Workout::factory()->forCategory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $workout->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_NAME_ATTRIBUTE_VALUE,
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $workout->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                'id' => $workout->getRouteKey(),
                self::MODEL_ATTRIBUTE_NAME        => self::MODEL_NAME_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_PERFORMANCE => $workout->performance,
                self::MODEL_ATTRIBUTE_COMMENTS    => $workout->comments,
                self::MODEL_ATTRIBUTE_CORRECTIONS => $workout->corrections,
                self::MODEL_ATTRIBUTE_WARNINGS    => $workout->warnings,
            ]
        );
    }

    /** @test */
    public function can_update_the_workout_performance_only()
    {
        $workout = Workout::factory()->forCategory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $workout->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_PERFORMANCE => self::MODEL_PERFORMANCE_ATTRIBUTE_VALUE,
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $workout->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                'id' => $workout->getRouteKey(),
                self::MODEL_ATTRIBUTE_NAME        => $workout->name,
                self::MODEL_ATTRIBUTE_PERFORMANCE => self::MODEL_PERFORMANCE_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_COMMENTS    => $workout->comments,
                self::MODEL_ATTRIBUTE_CORRECTIONS => $workout->corrections,
                self::MODEL_ATTRIBUTE_WARNINGS    => $workout->warnings,
            ]
        );
    }

    /** @test */
    public function can_update_the_workout_comments_only()
    {
        $workout = Workout::factory()->forCategory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $workout->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_COMMENTS => self::MODEL_COMMENTS_ATTRIBUTE_VALUE,
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $workout->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                'id' => $workout->getRouteKey(),
                self::MODEL_ATTRIBUTE_NAME        => $workout->name,
                self::MODEL_ATTRIBUTE_PERFORMANCE => $workout->performance,
                self::MODEL_ATTRIBUTE_COMMENTS    => self::MODEL_COMMENTS_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_CORRECTIONS => $workout->corrections,
                self::MODEL_ATTRIBUTE_WARNINGS    => $workout->warnings,
            ]
        );
    }

    /** @test */
    public function can_update_the_workout_corrections_only()
    {
        $workout = Workout::factory()->forCategory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $workout->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_CORRECTIONS => self::MODEL_CORRECTIONS_ATTRIBUTE_VALUE,
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $workout->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                'id' => $workout->getRouteKey(),
                self::MODEL_ATTRIBUTE_NAME        => $workout->name,
                self::MODEL_ATTRIBUTE_PERFORMANCE => $workout->performance,
                self::MODEL_ATTRIBUTE_COMMENTS    => $workout->comments,
                self::MODEL_ATTRIBUTE_CORRECTIONS => self::MODEL_CORRECTIONS_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_WARNINGS    => $workout->warnings,
            ]
        );
    }

    /** @test */
    public function can_update_the_workout_warnings_only()
    {
        $workout = Workout::factory()->forCategory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $workout->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_WARNINGS => self::MODEL_WARNINGS_ATTRIBUTE_VALUE,
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $workout->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                'id' => $workout->getRouteKey(),
                self::MODEL_ATTRIBUTE_NAME        => $workout->name,
                self::MODEL_ATTRIBUTE_PERFORMANCE => $workout->performance,
                self::MODEL_ATTRIBUTE_COMMENTS    => $workout->comments,
                self::MODEL_ATTRIBUTE_CORRECTIONS => $workout->corrections,
                self::MODEL_ATTRIBUTE_WARNINGS    => self::MODEL_WARNINGS_ATTRIBUTE_VALUE,
            ]
        );
    }

    /** @test */
    public function can_update_the_workout_category_only()
    {
        $workout = Workout::factory()->forCategory()->create();
        $newCategory = Category::factory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $workout->getRouteKey(),
            'relationships' => [
                'category' => [
                    'data' => [
                        'type' => 'categories',
                        'id' => (string) $newCategory->getRouteKey()
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $workout->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                'id'          => $workout->getRouteKey(),
                'category_id' => $newCategory->getRouteKey(),
                self::MODEL_ATTRIBUTE_NAME        => $workout->name,
                self::MODEL_ATTRIBUTE_PERFORMANCE => $workout->performance,
                self::MODEL_ATTRIBUTE_COMMENTS    => $workout->comments,
                self::MODEL_ATTRIBUTE_CORRECTIONS => $workout->corrections,
                self::MODEL_ATTRIBUTE_WARNINGS    => $workout->warnings,
            ]
        );
    }

    /** @test */
    public function can_update_the_workout_muscles_only()
    {
        $workout = Workout::factory()->forCategory()->create();
        $newCategory = Category::factory()->create();

        $muscles = Muscle::factory()->count(3)->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $workout->getRouteKey(),
            'relationships' => [
                'category' => [
                    'data' => [
                        'type' => 'categories',
                        'id' => (string) $newCategory->getRouteKey()
                    ]
                ],
                'muscles' => [
                    'data' => [
                        [
                            'type' => 'muscles',
                            'id' => (string) $muscles[0]->getRouteKey(),
                            'meta' => [
                                'pivot' => [
                                    'priority' => MusclePriorityEnum::PRINCIPAL
                                ]
                            ]
                        ],
                        [
                            'type' => 'muscles',
                            'id' => (string) $muscles[1]->getRouteKey(),
                            'meta' => [
                                'pivot' => [
                                    'priority' => MusclePriorityEnum::SECONDARY
                                ]
                            ]

                        ],
                        [
                            'type' => 'muscles',
                            'id' => (string) $muscles[2]->getRouteKey(),
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

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $workout->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas(
            'muscle_workout',
            [
                'workout_id' => $workout->getRouteKey(),
                'muscle_id'  => $muscles[0]->getRouteKey(),
                'priority'   => MusclePriorityEnum::PRINCIPAL
            ]
        );

        $this->assertDatabaseHas(
            'muscle_workout',
            [
                'workout_id' => $workout->getRouteKey(),
                'muscle_id'  => $muscles[1]->getRouteKey(),
                'priority'   => MusclePriorityEnum::SECONDARY
            ]
        );

        $this->assertDatabaseHas(
            'muscle_workout',
            [
                'workout_id' => $workout->getRouteKey(),
                'muscle_id'  => $muscles[2]->getRouteKey(),
                'priority'   => MusclePriorityEnum::ANTAGONIST
            ]
        );

        $dataToEdit = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $workout->getRouteKey(),
            'relationships' => [
                'category' => [
                    'data' => [
                        'type' => 'categories',
                        'id' => (string) $newCategory->getRouteKey()
                    ]
                ],
                'muscles' => [
                    'data' => [
                        [
                            'type' => 'muscles',
                            'id' => (string) $muscles[0]->getRouteKey(),
                            'meta' => [
                                'pivot' => [
                                    'priority' => MusclePriorityEnum::ANTAGONIST
                                ]
                            ]
                        ],
                        [
                            'type' => 'muscles',
                            'id' => (string) $muscles[1]->getRouteKey(),
                            'meta' => [
                                'pivot' => [
                                    'priority' => MusclePriorityEnum::PRINCIPAL
                                ]
                            ]

                        ],
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($dataToEdit)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $workout->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas(
            'muscle_workout',
            [
                'workout_id' => $workout->getRouteKey(),
                'muscle_id'  => $muscles[0]->getRouteKey(),
                'priority'   => MusclePriorityEnum::ANTAGONIST
            ]
        );

        $this->assertDatabaseHas(
            'muscle_workout',
            [
                'workout_id' => $workout->getRouteKey(),
                'muscle_id'  => $muscles[1]->getRouteKey(),
                'priority'   => MusclePriorityEnum::PRINCIPAL
            ]
        );

        $this->assertDatabaseMissing(
            'muscle_workout',
            [
                'workout_id' => $workout->getRouteKey(),
                'muscle_id'  => $muscles[2]->getRouteKey(),
                'priority'   => MusclePriorityEnum::ANTAGONIST
            ]
        );
    }

    /** @test */
    public function can_update_the_workout_image_only()
    {
        $workout = Workout::factory()->forCategory()->create();

        $file = UploadedFile::fake()->image($fileName = 'Test.jpg');

        $workout->addMedia($file)->toMediaCollection();

        $this->assertFileExists(storage_path(self::MODEL_IMAGE_ROUTE_PATH . $fileName));

        $this->assertDatabaseHas(
            'media',
            [
                'model_type'      => 'App\Models\Workout',
                'model_id'        => $workout->getRouteKey(),
                'collection_name' => 'default',
                'file_name'       => $fileName,
            ]
        );

        $workout->clearMediaCollection();

        // Verify that the image was not saved
        $this->assertFileDoesNotExist(storage_path(self::MODEL_IMAGE_ROUTE_PATH . $fileName));

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $workout->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_NAME_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_IMAGE => $this->file,
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $workout->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                'id' => $workout->getRouteKey(),
                self::MODEL_ATTRIBUTE_NAME        => self::MODEL_NAME_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_PERFORMANCE => $workout->performance,
                self::MODEL_ATTRIBUTE_COMMENTS    => $workout->comments,
                self::MODEL_ATTRIBUTE_CORRECTIONS => $workout->corrections,
                self::MODEL_ATTRIBUTE_WARNINGS    => $workout->warnings,
            ]
        );

        $this->assertDatabaseHas(
            'media',
            [
                'model_type'      => 'App\Models\Workout',
                'model_id'        => $workout->getRouteKey(),
                'collection_name' => 'default',
                'file_name'       => self::MODEL_ATTRIBUTE_IMAGE_NAME,
            ]
        );
    }
}
