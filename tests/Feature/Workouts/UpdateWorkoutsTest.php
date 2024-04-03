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

    const MODEL_NAME_ATTRIBUTE_VALUE = 'name changed';
    const MODEL_PERFORMANCE_ATTRIBUTE_VALUE = 'performance changed';
    const MODEL_COMMENTS_ATTRIBUTE_VALUE = 'comments changed';
    const MODEL_CORRECTIONS_ATTRIBUTE_VALUE = 'corrections changed';
    const MODEL_WARNINGS_ATTRIBUTE_VALUE = 'warnings changed';

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
    public function guests_users_cannot_update_workouts()
    {
        $workout = Workout::factory()->for($this->subcategory)->create();

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
        $workout = Workout::factory()->for($this->subcategory)->create();

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

        $this->assertDatabaseHas(self::MODEL_PLURAL_NAME, ['id' => $workout->getRouteKey(),
            self::MODEL_ATTRIBUTE_NAME        => self::MODEL_NAME_ATTRIBUTE_VALUE,
            self::MODEL_ATTRIBUTE_PERFORMANCE => self::MODEL_PERFORMANCE_ATTRIBUTE_VALUE,
            self::MODEL_ATTRIBUTE_COMMENTS    => self::MODEL_COMMENTS_ATTRIBUTE_VALUE,
            self::MODEL_ATTRIBUTE_CORRECTIONS => self::MODEL_CORRECTIONS_ATTRIBUTE_VALUE,
            self::MODEL_ATTRIBUTE_WARNINGS    => self::MODEL_WARNINGS_ATTRIBUTE_VALUE,
        ]);
    }

    /** @test */
    public function can_update_the_workout_name_only()
    {
        $workout = Workout::factory()->for($this->subcategory)->create();

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

        $this->assertDatabaseHas(self::MODEL_PLURAL_NAME, ['id' => $workout->getRouteKey(),
            self::MODEL_ATTRIBUTE_NAME        => self::MODEL_NAME_ATTRIBUTE_VALUE,
            self::MODEL_ATTRIBUTE_PERFORMANCE => $workout->performance,
            self::MODEL_ATTRIBUTE_COMMENTS    => $workout->comments,
            self::MODEL_ATTRIBUTE_CORRECTIONS => $workout->corrections,
            self::MODEL_ATTRIBUTE_WARNINGS    => $workout->warnings,
        ]);
    }

    /** @test */
    public function can_update_the_workout_performance_only()
    {
        $workout = Workout::factory()->for($this->subcategory)->create();

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

        $this->assertDatabaseHas(self::MODEL_PLURAL_NAME, ['id' => $workout->getRouteKey(),
            self::MODEL_ATTRIBUTE_NAME        => $workout->name,
            self::MODEL_ATTRIBUTE_PERFORMANCE => self::MODEL_PERFORMANCE_ATTRIBUTE_VALUE,
            self::MODEL_ATTRIBUTE_COMMENTS    => $workout->comments,
            self::MODEL_ATTRIBUTE_CORRECTIONS => $workout->corrections,
            self::MODEL_ATTRIBUTE_WARNINGS    => $workout->warnings,
        ]);
    }

    /** @test */
    public function can_update_the_workout_comments_only()
    {
        $workout = Workout::factory()->for($this->subcategory)->create();

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

        $this->assertDatabaseHas(self::MODEL_PLURAL_NAME, ['id' => $workout->getRouteKey(),
            self::MODEL_ATTRIBUTE_NAME        => $workout->name,
            self::MODEL_ATTRIBUTE_PERFORMANCE => $workout->performance,
            self::MODEL_ATTRIBUTE_COMMENTS    => self::MODEL_COMMENTS_ATTRIBUTE_VALUE,
            self::MODEL_ATTRIBUTE_CORRECTIONS => $workout->corrections,
            self::MODEL_ATTRIBUTE_WARNINGS    => $workout->warnings,
        ]);
    }

    /** @test */
    public function can_update_the_workout_corrections_only()
    {
        $workout = Workout::factory()->for($this->subcategory)->create();

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

        $this->assertDatabaseHas(self::MODEL_PLURAL_NAME, ['id' => $workout->getRouteKey(),
            self::MODEL_ATTRIBUTE_NAME        => $workout->name,
            self::MODEL_ATTRIBUTE_PERFORMANCE => $workout->performance,
            self::MODEL_ATTRIBUTE_COMMENTS    => $workout->comments,
            self::MODEL_ATTRIBUTE_CORRECTIONS => self::MODEL_CORRECTIONS_ATTRIBUTE_VALUE,
            self::MODEL_ATTRIBUTE_WARNINGS    => $workout->warnings,
        ]);
    }

    /** @test */
    public function can_update_the_workout_warnings_only()
    {
        $workout = Workout::factory()->for($this->subcategory)->create();

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

        $this->assertDatabaseHas(self::MODEL_PLURAL_NAME, ['id' => $workout->getRouteKey(),
            self::MODEL_ATTRIBUTE_NAME        => $workout->name,
            self::MODEL_ATTRIBUTE_PERFORMANCE => $workout->performance,
            self::MODEL_ATTRIBUTE_COMMENTS    => $workout->comments,
            self::MODEL_ATTRIBUTE_CORRECTIONS => $workout->corrections,
            self::MODEL_ATTRIBUTE_WARNINGS    => self::MODEL_WARNINGS_ATTRIBUTE_VALUE,
        ]);
    }

    /** @test */
    public function can_update_the_workout_subcategory_only()
    {
        $workout = Workout::factory()->for($this->subcategory)->create();
        $newSubcategory = Subcategory::factory()->forCategory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $workout->getRouteKey(),
            'relationships' => [
                'subcategory' => [
                    'data' => [
                        'type' => 'subcategories',
                        'id' => (string) $newSubcategory->getRouteKey()
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $workout->getRouteKey()));

        // Success (200)
        $response->assertStatus(200);

        $this->assertDatabaseHas(self::MODEL_PLURAL_NAME, [
            'id'             => $workout->getRouteKey(),
            'subcategory_id' => $newSubcategory->getRouteKey(),
            self::MODEL_ATTRIBUTE_NAME        => $workout->name,
            self::MODEL_ATTRIBUTE_PERFORMANCE => $workout->performance,
            self::MODEL_ATTRIBUTE_COMMENTS    => $workout->comments,
            self::MODEL_ATTRIBUTE_CORRECTIONS => $workout->corrections,
            self::MODEL_ATTRIBUTE_WARNINGS    => $workout->warnings,
        ]);
    }
}
