<?php

namespace Tests\Feature\Routines;

use App\Models\Routine;
use App\Models\Category;
use App\Models\User;
use App\Models\Workout;
use Database\Seeders\permissionsSeeders\RoutinesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CreateRoutinesTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'routines';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.store';

    const BELONGS_TO_MANY_WORKOUTS_RELATIONSHIP_SINGLE_NAME = 'workout';
    const BELONGS_TO_MANY_WORKOUTS_RELATIONSHIP_PLURAL_NAME = 'workouts';

    const PIVOT_TABLE_ROUTINE_WORKOUT = 'routine_workout';

    const MODEL_ATTRIBUTE_NAME = 'name';

    protected User $user;
    protected Category $category;

    // For making relationship test with 3 workouts
    protected Workout $workout1;
    protected Workout $workout2;
    protected Workout $workout3;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(RoutinesPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('user');
        $this->category = Category::factory()->create();

        // For making relationship test with 3 muscles
        $this->workout1 = Workout::factory()->for($this->category)->create();
        $this->workout2 = Workout::factory()->for($this->category)->create();
        $this->workout3 = Workout::factory()->for($this->category)->create();
    }

    /** @test */
    public function guests_users_cannot_create_routines()
    {
        $routine = array_filter(Routine::factory()->raw());

        $data =  [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $routine
        ];

        $response = $this->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unauthorized (401)
        $response->assertStatus(401);

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $routine);
    }

    /** @test */
    public function authenticated_users_can_create_routines()
    {
        $routine = array_filter(Routine::factory()->raw());

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $routine);

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $routine
        ];

        $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE))
            ->assertCreated();

        $this->assertDatabaseHas(self::MODEL_PLURAL_NAME, $routine);
    }

    /** @test */
    public function authenticated_users_can_create_routines_including_workouts()
    {
        $routine = array_filter(Routine::factory()->raw());

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $routine);

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $routine,
            'relationships' => [
                self::BELONGS_TO_MANY_WORKOUTS_RELATIONSHIP_PLURAL_NAME => [
                    'data' => [
                        [
                            'type' => self::BELONGS_TO_MANY_WORKOUTS_RELATIONSHIP_PLURAL_NAME,
                            'id' => (string) $this->workout1->getRouteKey(),
                            'meta' => [
                                'pivot' => [
                                    'series'      => 3,
                                    'repetitions' => 10,
                                    'time'        => 60
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::BELONGS_TO_MANY_WORKOUTS_RELATIONSHIP_PLURAL_NAME)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE))
            ->assertCreated();

        $routineId = Routine::whereName($routine[self::MODEL_ATTRIBUTE_NAME])->first()->getRouteKey();

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                'id' => $routineId,
                self::MODEL_ATTRIBUTE_NAME => $routine[self::MODEL_ATTRIBUTE_NAME]
            ]
        );

        // Verify BelongsToMany relationship with Workout model
        $this->assertDatabaseHas(
            self::PIVOT_TABLE_ROUTINE_WORKOUT,
            [
                'routine_id'  => $routineId,
                'workout_id'  => $this->workout1->getRouteKey(),
                'series'      => 3,
                'repetitions' => 10,
                'time'        => 60
            ]
        );
    }

    /** @test */
    public function authenticated_users_can_create_routines_including_3_workouts()
    {
        $routine = array_filter(Routine::factory()->raw());

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $routine);

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $routine,
            'relationships' => [
                self::BELONGS_TO_MANY_WORKOUTS_RELATIONSHIP_PLURAL_NAME => [
                    'data' => [
                        [
                            'type' => self::BELONGS_TO_MANY_WORKOUTS_RELATIONSHIP_PLURAL_NAME,
                            'id' => (string) $this->workout1->getRouteKey(),
                            'meta' => [
                                'pivot' => [
                                    'series'      => 3,
                                    'repetitions' => 10,
                                    'time'        => 60
                                ]
                            ]
                        ],
                        [
                            'type' => self::BELONGS_TO_MANY_WORKOUTS_RELATIONSHIP_PLURAL_NAME,
                            'id' => (string) $this->workout2->getRouteKey(),
                            'meta' => [
                                'pivot' => [
                                    'series'      => 3,
                                    'repetitions' => 10,
                                    'time'        => 60
                                ]
                            ]
                        ],
                        [
                            'type' => self::BELONGS_TO_MANY_WORKOUTS_RELATIONSHIP_PLURAL_NAME,
                            'id' => (string) $this->workout3->getRouteKey(),
                            'meta' => [
                                'pivot' => [
                                    'series'      => 3,
                                    'repetitions' => 10,
                                    'time'        => 60
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::BELONGS_TO_MANY_WORKOUTS_RELATIONSHIP_PLURAL_NAME)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE))
            ->assertCreated();

        $routineId = Routine::whereName($routine[self::MODEL_ATTRIBUTE_NAME])->first()->getRouteKey();

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                'id' => $routineId,
                self::MODEL_ATTRIBUTE_NAME => $routine[self::MODEL_ATTRIBUTE_NAME]
            ]
        );

        // Verify BelongsToMany relationship with Workout model
        $this->assertDatabaseHas(
            self::PIVOT_TABLE_ROUTINE_WORKOUT,
            [
                'routine_id'  => $routineId,
                'workout_id'  => $this->workout1->getRouteKey(),
                'series'      => 3,
                'repetitions' => 10,
                'time'        => 60
            ]
        );

        $this->assertDatabaseHas(
            self::PIVOT_TABLE_ROUTINE_WORKOUT,
            [
                'routine_id'  => $routineId,
                'workout_id'  => $this->workout2->getRouteKey(),
                'series'      => 3,
                'repetitions' => 10,
                'time'        => 60
            ]
        );

        $this->assertDatabaseHas(
            self::PIVOT_TABLE_ROUTINE_WORKOUT,
            [
                'routine_id'  => $routineId,
                'workout_id'  => $this->workout3->getRouteKey(),
                'series'      => 3,
                'repetitions' => 10,
                'time'        => 60
            ]
        );
    }

    /** @test */
    public function routine_name_is_required()
    {
        $routine = array_filter(Routine::factory()->raw(
            [
                self::MODEL_ATTRIBUTE_NAME => ''
            ]
        ));

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => [
                self::MODEL_ATTRIBUTE_NAME => ''
            ]
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

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $routine);
    }
}
