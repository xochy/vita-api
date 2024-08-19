<?php

namespace Tests\Feature\Workouts;

use App\Models\Category;
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

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(WorkoutsPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function it_can_fetch_single_workout()
    {
        $workout = Workout::factory()->forCategory()->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $workout));

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
                    'slug'        => $workout->slug,
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
        $workouts = Workout::factory()->forCategory()->count(3)->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->get(route(self::MODEL_INDEX_ACTION_ROUTE));

        $response->assertFetchedMany(
            $workouts->map(
                fn (Workout $workout) => [
                    'type' => self::MODEL_PLURAL_NAME,
                    'id' => (string) $workout->getRouteKey(),
                    'attributes' => [
                        'name'        => $workout->name,
                        'performance' => $workout->performance,
                        'comments'    => $workout->comments,
                        'corrections' => $workout->corrections,
                        'warnings'    => $workout->warnings,
                        'slug'        => $workout->slug,
                    ],
                    'links' => [
                        'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $workout)
                    ]
                ]
            )->all()
        );
    }
}
