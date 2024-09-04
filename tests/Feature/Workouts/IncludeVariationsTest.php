<?php

namespace Tests\Feature\Workouts;

use App\Models\User;
use App\Models\Variation;
use App\Models\Workout;
use Database\Seeders\permissionsSeeders\WorkoutsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IncludeVariationsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'workouts';
    const MODEL_INCLUDE_RELATIONSHIP_NAME = 'variations';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';
    const MODEL_SHOW_RELATIONSHIP_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.variations';

    const MODEL_RELATED_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME
        . '.' . self::MODEL_INCLUDE_RELATIONSHIP_NAME;

    const MODEL_SELF_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME
        . '.' . self::MODEL_INCLUDE_RELATIONSHIP_NAME . '.show';

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
    public function workouts_can_include_variations()
    {
        $workout = Workout::factory()->forCategory()->create();
        Variation::factory()->for($workout)->count(3)->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->get(route(self::MODEL_MAIN_ACTION_ROUTE, $workout));

        $response->assertSee($workout->variations[0]->getRouteKey());

        $response->assertJsonFragment(
            [
                'related' => route(self::MODEL_RELATED_ROUTE, $workout)
            ]
        );

        $response->assertJsonFragment(
            [
                'self' => route(self::MODEL_SELF_ROUTE, $workout)
            ]
        );
    }

    /** @test */
    public function workouts_can_fetch_related_variations()
    {
        $workout = Workout::factory()->forCategory()->create();
        $variations = Variation::factory()->for($workout)->count(3)->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_INCLUDE_RELATIONSHIP_NAME)
            ->get(route(self::MODEL_SHOW_RELATIONSHIP_ROUTE, $workout));

        $response->assertJsonCount(3, 'data');

        $response->assertJsonPath('data.0.attributes.name', $variations[0]->name);
        $response->assertJsonPath('data.1.attributes.name', $variations[1]->name);
        $response->assertJsonPath('data.2.attributes.name', $variations[2]->name);

    }
}
