<?php

namespace Tests\Feature\Variations;

use App\Models\User;
use App\Models\Variation;
use App\Models\Workout;
use Database\Seeders\permissionsSeeders\VariationsPermissionsSeeder;
use Database\Seeders\permissionsSeeders\WorkoutsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IncludeWorkoutTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'variations';
    const MODEL_INCLUDE_RELATIONSHIP_NAME = 'workout';
    const MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME = 'workouts';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';

    const MODEL_RELATED_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME
        . '.' . self::MODEL_INCLUDE_RELATIONSHIP_NAME;

    const MODEL_SELF_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME
        . '.' . self::MODEL_INCLUDE_RELATIONSHIP_NAME . '.show';

    protected User $user;
    protected Workout $workout;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(VariationsPermissionsSeeder::class);
            $this->seed(WorkoutsPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
        $this->workout = Workout::factory()->forCategory()->create();
    }

    /** @test */
    public function variations_can_include_workout()
    {
        $variation = Variation::factory()->for($this->workout)->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_NAME)
            ->get(route(self::MODEL_MAIN_ACTION_ROUTE, $variation));

        $response->assertSee($variation->workout->getRouteKey());

        $response->assertJsonFragment(
            [
                'related' => route(self::MODEL_RELATED_ROUTE, $variation)
            ]
        );
        $response->assertJsonFragment(
            [
                'self' => route(self::MODEL_SELF_ROUTE, $variation)
            ]
        );
    }

    /** @test */
    public function variations_can_fetch_related_workout()
    {
        $variation = Variation::factory()->for($this->workout)->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME)
            ->get(route(self::MODEL_SELF_ROUTE, $variation));

        $response->assertFetchedOne($variation->workout);
    }
}
