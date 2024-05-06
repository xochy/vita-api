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

class ListWorkoutsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'workouts';
    const MODEL_SHOW_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';
    const MODEL_INDEX_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.index';

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
    public function it_can_fetch_single_workout()
    {
        $workout = Workout::factory()->for($this->subcategory)->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $workout));

        $response->assertFetchedOne($workout);

        $response->assertFetchedOne(
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $workout->getRouteKey(),
                'attributes' => [
                    'name'        => $workout->name,
                    'performance' => $workout->performance,
                    'comments'    => $workout->comments,
                    'corrections' => $workout->corrections,
                    'warnings'    => $workout->warnings,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $workout)
                ]
            ]
        );
    }

    /** @test */
    public function can_fetch_all_workouts()
    {
        $workouts = Workout::factory()->for($this->subcategory)->count(3)->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->get(route(self::MODEL_INDEX_ACTION_ROUTE));

        $response->assertFetchedMany($workouts);

        $response->assertFetchedMany(
            [
                [
                    'type' => self::MODEL_PLURAL_NAME,
                    'id' => (string) $workouts[0]->getRouteKey(),
                    'attributes' => [
                        'name'        => $workouts[0]->name,
                        'performance' => $workouts[0]->performance,
                        'comments'    => $workouts[0]->comments,
                        'corrections' => $workouts[0]->corrections,
                        'warnings'    => $workouts[0]->warnings,
                    ],
                    'links' => [
                        'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $workouts[0])
                    ]
                ],
                [
                    'type' => self::MODEL_PLURAL_NAME,
                    'id' => (string) $workouts[1]->getRouteKey(),
                    'attributes' => [
                        'name'        => $workouts[1]->name,
                        'performance' => $workouts[1]->performance,
                        'comments'    => $workouts[1]->comments,
                        'corrections' => $workouts[1]->corrections,
                        'warnings'    => $workouts[1]->warnings,
                    ],
                    'links' => [
                        'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $workouts[1])
                    ]
                ],
                [
                    'type' => self::MODEL_PLURAL_NAME,
                    'id' => (string) $workouts[2]->getRouteKey(),
                    'attributes' => [
                        'name'        => $workouts[2]->name,
                        'performance' => $workouts[2]->performance,
                        'comments'    => $workouts[2]->comments,
                        'corrections' => $workouts[2]->corrections,
                        'warnings'    => $workouts[2]->warnings,
                    ],
                    'links' => [
                        'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $workouts[2])
                    ]
                ],
            ]
        );
    }
}
