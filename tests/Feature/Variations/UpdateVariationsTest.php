<?php

namespace Tests\Feature\Variations;

use App\Models\Muscle;
use App\Models\User;
use App\Models\Variation;
use App\Models\Workout;
use Database\Seeders\permissionsSeeders\VariationsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UpdateVariationsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'variations';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.update';

    const MODEL_ATTRIBUTE_NAME = 'name';
    const MODEL_ATTRIBUTE_PERFORMANCE = 'performance';
    const MODEL_ATTRIBUTE_IMAGE = 'image';
    const MODEL_ATTRIBUTE_IMAGE_NAME = 'Test.jpg';
    const MODEL_IMAGE_ROUTE_PATH = 'app/public/1/';

    const MODEL_NAME_ATTRIBUTE_VALUE = 'name changed';
    const MODEL_PERFORMANCE_ATTRIBUTE_VALUE = 'performance changed';

    protected User $user;
    protected Workout $workout;
    protected UploadedFile $file;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(VariationsPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
        $this->workout = Workout::factory()->forCategory()->create();

        Storage::disk('public')->deleteDirectory('.');

        $this->file = UploadedFile::fake()->image(self::MODEL_ATTRIBUTE_IMAGE_NAME);
    }

    /** @test */
    public function guests_users_cannot_update_variations()
    {
        $variation = Variation::factory()->for($this->workout)->create();

        $data = [
            'data' => [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $variation->getRouteKey(),
                'attributes' => [
                    self::MODEL_ATTRIBUTE_NAME => self::MODEL_NAME_ATTRIBUTE_VALUE,
                    self::MODEL_ATTRIBUTE_PERFORMANCE => self::MODEL_PERFORMANCE_ATTRIBUTE_VALUE,
                ],
            ],
        ];

        $response = $this->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $variation->getRouteKey()));

        // Unauthorized (401)
        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_users_as_admin_can_update_variations()
    {
        $variation = Variation::factory()->for($this->workout)->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $variation->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_NAME_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_PERFORMANCE => self::MODEL_PERFORMANCE_ATTRIBUTE_VALUE,
            ],
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $variation->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                'id' => $variation->getKey(),
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_NAME_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_PERFORMANCE => self::MODEL_PERFORMANCE_ATTRIBUTE_VALUE,
            ]
        );
    }

    /** @test */
    public function can_update_the_variation_name_only()
    {
        $variation = Variation::factory()->for($this->workout)->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $variation->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_NAME_ATTRIBUTE_VALUE,
            ],
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $variation->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                'id' => $variation->getKey(),
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_NAME_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_PERFORMANCE => $variation->performance,
            ]
        );
    }

    /** @test */
    public function can_update_the_variation_performance_only()
    {
        $variation = Variation::factory()->for($this->workout)->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $variation->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_PERFORMANCE => self::MODEL_PERFORMANCE_ATTRIBUTE_VALUE,
            ],
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $variation->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                'id' => $variation->getKey(),
                self::MODEL_ATTRIBUTE_NAME => $variation->name,
                self::MODEL_ATTRIBUTE_PERFORMANCE => self::MODEL_PERFORMANCE_ATTRIBUTE_VALUE,
            ]
        );
    }

    /** @test */
    public function can_update_the_variation_workout_only()
    {
        $variation = Variation::factory()->for($this->workout)->create();
        $newWorkout = Workout::factory()->forCategory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $variation->getRouteKey(),
            'relationships' => [
                'workout' => [
                    'data' => [
                        'type' => 'workouts',
                        'id' => (string) $newWorkout->getRouteKey(),
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $variation->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                'id'          => $variation->getKey(),
                'workout_id'  => $newWorkout->getKey(),
                'name'        => $variation->name,
                'performance' => $variation->performance,
            ]
        );
    }

    /** @test */
    public function can_update_the_variation_muscles_only()
    {
        $variation = Variation::factory()->for($this->workout)->create();
        $muscles = Muscle::factory(2)->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $variation->getRouteKey(),
            'relationships' => [
                'muscles' => [
                    'data' => [
                        [
                            'type' => 'muscles',
                            'id' => (string) $muscles[0]->getRouteKey(),
                        ],
                        [
                            'type' => 'muscles',
                            'id' => (string) $muscles[1]->getRouteKey(),
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $variation->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas(
            'muscle_variation',
            [
                'variation_id' => $variation->getKey(),
                'muscle_id'    => $muscles[0]->getKey(),
            ]
        );

        $this->assertDatabaseHas(
            'muscle_variation',
            [
                'variation_id' => $variation->getKey(),
                'muscle_id'    => $muscles[1]->getKey(),
            ]
        );
    }

    /** @test */
    public function can_update_the_variation_image_only()
    {
        $variation = Variation::factory()->for($this->workout)->create();

        $file = UploadedFile::fake()->image($fileName = 'Test.jpg');

        $variation->addMedia($file)->toMediaCollection();

        $this->assertFileExists(storage_path(self::MODEL_IMAGE_ROUTE_PATH . $fileName));

        $this->assertDatabaseHas(
            'media',
            [
                'model_type'      => 'App\Models\Variation',
                'model_id'        => $variation->getRouteKey(),
                'collection_name' => 'default',
                'file_name'       => $fileName,
            ]
        );

        $variation->clearMediaCollection();

        // Verify that the image was not saved
        $this->assertFileDoesNotExist(storage_path(self::MODEL_IMAGE_ROUTE_PATH . $fileName));

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $variation->getRouteKey(),
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME => self::MODEL_NAME_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_IMAGE => $this->file,
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $variation->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                'id' => $variation->getRouteKey(),
                self::MODEL_ATTRIBUTE_NAME        => self::MODEL_NAME_ATTRIBUTE_VALUE,
                self::MODEL_ATTRIBUTE_PERFORMANCE => $variation->performance,
            ]
        );

        $this->assertDatabaseHas(
            'media',
            [
                'model_type'      => 'App\Models\Variation',
                'model_id'        => $variation->getRouteKey(),
                'collection_name' => 'default',
                'file_name'       => self::MODEL_ATTRIBUTE_IMAGE_NAME,
            ]
        );
    }
}
