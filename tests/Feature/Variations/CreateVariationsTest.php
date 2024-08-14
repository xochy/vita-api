<?php

namespace Tests\Feature\Variations;

use App\Models\Muscle;
use App\Models\User;
use App\Models\Variation;
use App\Models\Workout;
use Database\Seeders\RoleSeeder;
use Database\Seeders\permissionsSeeders\VariationsPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CreateVariationsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'variations';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.store';

    const BELONGS_TO_WORKOUT_RELATIONSHIP_SINGLE_NAME = 'workout';
    const BELONGS_TO_WORKOUT_RELATIONSHIP_PLURAL_NAME = 'workouts';

    const BELONGS_TO_MANY_MUSCLES_RELATIONSHIP_SINGLE_NAME = 'muscle';
    const BELONGS_TO_MANY_MUSCLES_RELATIONSHIP_PLURAL_NAME = 'muscles';

    const PIVOT_TABLE_MUSCLE_WORKOUT = 'muscle_variation';

    const MODEL_ATTRIBUTE_NAME = 'name';
    const MODEL_ATTRIBUTE_PERFORMANCE = 'performance';
    const MODEL_ATTRIBUTE_IMAGE = 'image';
    const MODEL_IMAGE_ROUTE_PATH = 'app/public/1/';

    protected User $user;
    protected Workout $workout;

    // For making relationship test with 3 muscles
    protected Muscle $muscle1;
    protected Muscle $muscle2;
    protected Muscle $muscle3;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(VariationsPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
        $this->workout = Workout::factory()->forCategory()->create();

        // For making relationship test with 3 muscles
        $this->muscle1 = Muscle::factory()->create();
        $this->muscle2 = Muscle::factory()->create();
        $this->muscle3 = Muscle::factory()->create();

        Storage::disk('public')->deleteDirectory('.');
    }

    /** @test */
    public function guests_users_cannot_create_variations()
    {
        $file = UploadedFile::fake()->image($fileName = Str::uuid()->toString() . '.jpg');

        $variation = array_filter(
            Variation::factory()->raw(
                [
                    self::MODEL_ATTRIBUTE_IMAGE => $file
                ]
            )
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $variation
        ];

        $response = $this->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unauthorized (401)
        $response->assertStatus(401);

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $variation);

        // Verify that the image was not saved
        $this->assertFileDoesNotExist(storage_path(self::MODEL_IMAGE_ROUTE_PATH . $fileName));
    }

    /** @test */
    public function authenticated_users_as_admin_can_create_variations_including_workout()
    {
        $file = UploadedFile::fake()->image($fileName = Str::uuid()->toString() . '.jpg');

        $variation = array_filter(
            Variation::factory()->raw(
                [
                    self::MODEL_ATTRIBUTE_IMAGE => $file
                ]
            )
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $variation);

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $variation,
            'relationships' => [
                self::BELONGS_TO_WORKOUT_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::BELONGS_TO_WORKOUT_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->workout->getRouteKey()
                    ]
                ],
            ]
        ];

        $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::BELONGS_TO_WORKOUT_RELATIONSHIP_SINGLE_NAME)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE))
            ->assertCreated();

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                'id' => Variation::whereName($variation[self::MODEL_ATTRIBUTE_NAME])->first()->getRouteKey(),
                'workout_id' => $this->workout->getRouteKey(),
                self::MODEL_ATTRIBUTE_NAME => $variation[self::MODEL_ATTRIBUTE_NAME],
                self::MODEL_ATTRIBUTE_PERFORMANCE => $variation[self::MODEL_ATTRIBUTE_PERFORMANCE],
            ]
        );

        $this->assertFileExists(storage_path(self::MODEL_IMAGE_ROUTE_PATH . $fileName));
    }

    /** @test */
    public function authenticated_users_as_admin_can_create_variations_including_muscles()
    {
        $file = UploadedFile::fake()->image($fileName = Str::uuid()->toString() . '.jpg');

        $variation = array_filter(
            Variation::factory()->raw(
                [
                    self::MODEL_ATTRIBUTE_IMAGE => $file
                ]
            )
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $variation);

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $variation,
            'relationships' => [
                self::BELONGS_TO_WORKOUT_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::BELONGS_TO_WORKOUT_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->workout->getRouteKey()
                    ]
                ],
                self::BELONGS_TO_MANY_MUSCLES_RELATIONSHIP_PLURAL_NAME => [
                    'data' => [
                        [
                            'type' => self::BELONGS_TO_MANY_MUSCLES_RELATIONSHIP_PLURAL_NAME,
                            'id' => (string) $this->muscle1->getRouteKey(),
                        ]
                    ]
                ]
            ]
        ];

        $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::BELONGS_TO_WORKOUT_RELATIONSHIP_SINGLE_NAME)
            ->includePaths(self::BELONGS_TO_MANY_MUSCLES_RELATIONSHIP_PLURAL_NAME)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE))
            ->assertCreated();

        $variationId = Variation::whereName($variation[self::MODEL_ATTRIBUTE_NAME])->first()->getRouteKey();

        // Verify BelongsTo relationship with category model and Workout data
        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                'id' => $variationId,
                'workout_id' => $this->workout->getRouteKey(),
                self::MODEL_ATTRIBUTE_NAME => $variation[self::MODEL_ATTRIBUTE_NAME],
                self::MODEL_ATTRIBUTE_PERFORMANCE => $variation[self::MODEL_ATTRIBUTE_PERFORMANCE],
            ]
        );

        // Verify BelongsToMany relationship with Muscle model
        $this->assertDatabaseHas(
            self::PIVOT_TABLE_MUSCLE_WORKOUT,
            [
                'muscle_id' => $this->muscle1->getRouteKey(),
                'variation_id' => $variationId,
            ]
        );

        $this->assertFileExists(storage_path(self::MODEL_IMAGE_ROUTE_PATH . $fileName));
    }

    /** @test */
    public function authenticated_users_as_admin_can_create_variations_including_3_muscles()
    {
        $file = UploadedFile::fake()->image($fileName = Str::uuid()->toString() . '.jpg');

        $variation = array_filter(
            Variation::factory()->raw(
                [
                    self::MODEL_ATTRIBUTE_IMAGE => $file
                ]
            )
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $variation);

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $variation,
            'relationships' => [
                self::BELONGS_TO_WORKOUT_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::BELONGS_TO_WORKOUT_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->workout->getRouteKey()
                    ]
                ],
                self::BELONGS_TO_MANY_MUSCLES_RELATIONSHIP_PLURAL_NAME => [
                    'data' => [
                        [
                            'type' => self::BELONGS_TO_MANY_MUSCLES_RELATIONSHIP_PLURAL_NAME,
                            'id' => (string) $this->muscle1->getRouteKey(),
                        ],
                        [
                            'type' => self::BELONGS_TO_MANY_MUSCLES_RELATIONSHIP_PLURAL_NAME,
                            'id' => (string) $this->muscle2->getRouteKey(),
                        ],
                        [
                            'type' => self::BELONGS_TO_MANY_MUSCLES_RELATIONSHIP_PLURAL_NAME,
                            'id' => (string) $this->muscle3->getRouteKey(),
                        ]
                    ]
                ]
            ]
        ];

        $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::BELONGS_TO_WORKOUT_RELATIONSHIP_SINGLE_NAME)
            ->includePaths(self::BELONGS_TO_MANY_MUSCLES_RELATIONSHIP_PLURAL_NAME)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE))
            ->assertCreated();

        $variationId = Variation::whereName($variation[self::MODEL_ATTRIBUTE_NAME])->first()->getRouteKey();

        // Verify BelongsTo relationship with category model and Workout data
        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                'id' => $variationId,
                'workout_id' => $this->workout->getRouteKey(),
                self::MODEL_ATTRIBUTE_NAME => $variation[self::MODEL_ATTRIBUTE_NAME],
                self::MODEL_ATTRIBUTE_PERFORMANCE => $variation[self::MODEL_ATTRIBUTE_PERFORMANCE],
            ]
        );

        // Verify BelongsToMany relationship with Muscle model
        $this->assertDatabaseHas(
            self::PIVOT_TABLE_MUSCLE_WORKOUT,
            [
                'muscle_id' => $this->muscle1->getRouteKey(),
                'variation_id' => $variationId,
            ]
        );

        $this->assertDatabaseHas(
            self::PIVOT_TABLE_MUSCLE_WORKOUT,
            [
                'muscle_id' => $this->muscle2->getRouteKey(),
                'variation_id' => $variationId,
            ]
        );

        $this->assertDatabaseHas(
            self::PIVOT_TABLE_MUSCLE_WORKOUT,
            [
                'muscle_id' => $this->muscle3->getRouteKey(),
                'variation_id' => $variationId,
            ]
        );

        $this->assertFileExists(storage_path(self::MODEL_IMAGE_ROUTE_PATH . $fileName));
    }

    /** @test */
    public function authenticated_users_as_user_cannot_create_variations()
    {
        $file = UploadedFile::fake()->image($fileName = Str::uuid()->toString() . '.jpg');

        $variation = array_filter(
            Variation::factory()->raw(
                [
                    self::MODEL_ATTRIBUTE_IMAGE => $file
                ]
            )
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $variation,
            'relationships' => [
                self::BELONGS_TO_WORKOUT_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::BELONGS_TO_WORKOUT_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->workout->getRouteKey()
                    ]
                ]
            ]
        ];

        $user = User::factory()->create()->assignRole('user');

        $response = $this->actingAs($user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::BELONGS_TO_WORKOUT_RELATIONSHIP_SINGLE_NAME)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Forbidden (403)
        $response->assertStatus(403);

        $this->assertDatabaseMissing(
            self::MODEL_PLURAL_NAME,
            [
                self::MODEL_ATTRIBUTE_NAME => $variation[self::MODEL_ATTRIBUTE_NAME],
                self::MODEL_ATTRIBUTE_PERFORMANCE => $variation[self::MODEL_ATTRIBUTE_PERFORMANCE],
            ]
        );

        // Verify that the image was not saved
        $this->assertFileDoesNotExist(storage_path(self::MODEL_IMAGE_ROUTE_PATH . $fileName));
    }

    /** @test */
    public function variation_name_is_required()
    {
        $file = UploadedFile::fake()->image($fileName = Str::uuid()->toString() . '.jpg');

        $variation = array_filter(
            Variation::factory()->raw(
                [
                    self::MODEL_ATTRIBUTE_NAME => '',
                    self::MODEL_ATTRIBUTE_IMAGE => $file
                ]
            )
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $variation,
            'relationships' => [
                self::BELONGS_TO_WORKOUT_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::BELONGS_TO_WORKOUT_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->workout->getRouteKey()
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::BELONGS_TO_WORKOUT_RELATIONSHIP_SINGLE_NAME)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => '/data/attributes/name'],
                'detail' => 'The name field is required.'
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $variation);

        // Verify that the image was not saved
        $this->assertFileDoesNotExist(storage_path(self::MODEL_IMAGE_ROUTE_PATH . $fileName));
    }

    /** @test */
    public function variation_performance_is_required()
    {
        $file = UploadedFile::fake()->image($fileName = Str::uuid()->toString() . '.jpg');

        $variation = array_filter(
            Variation::factory()->raw(
                [
                    self::MODEL_ATTRIBUTE_PERFORMANCE => '',
                    self::MODEL_ATTRIBUTE_IMAGE => $file
                ]
            )
        );

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $variation,
            'relationships' => [
                self::BELONGS_TO_WORKOUT_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::BELONGS_TO_WORKOUT_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->workout->getRouteKey()
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::BELONGS_TO_WORKOUT_RELATIONSHIP_SINGLE_NAME)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(
            422,
            [
                'source' => ['pointer' => '/data/attributes/performance'],
                'detail' => 'The performance field is required.'
            ]
        );

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $variation);

        // Verify that the image was not saved
        $this->assertFileDoesNotExist(storage_path(self::MODEL_IMAGE_ROUTE_PATH . $fileName));
    }
}
