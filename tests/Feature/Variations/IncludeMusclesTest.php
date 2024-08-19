<?php

namespace Tests\Feature\Variations;

use App\Models\Muscle;
use App\Models\User;
use App\Models\Variation;
use App\Models\Workout;
use Database\Seeders\permissionsSeeders\MusclesPermissionsSeeder;
use Database\Seeders\permissionsSeeders\VariationsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IncludeMusclesTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'variations';
    const MODEL_INCLUDE_RELATIONSHIP_NAME = 'muscle';
    const MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME = 'muscles';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';

    const MODEL_RELATED_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME
        . '.' . self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME;

    const MODEL_SELF_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME
        . '.' . self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME . '.show';

    protected User $user;
    protected Workout $workout;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(VariationsPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
        $this->workout = Workout::factory()->forCategory()->create();
    }

    /** @test */
    public function variations_can_include_muscles()
    {
        $variation = Variation::factory()->for($this->workout)
            ->hasAttached(Muscle::factory())
            ->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME)
            ->get(route(self::MODEL_MAIN_ACTION_ROUTE, $variation));

        $response->assertSee($variation->muscles[0]->name);

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

        $this->assertDatabaseHas(
            'muscle_variation',
            [
                'variation_id' => $variation->id,
            ]
        );
    }

    /** @test */
    public function variations_can_fetch_related_muscles()
    {
        $variation = Variation::factory()->for($this->workout)
            ->hasAttached(Muscle::factory())
            ->hasAttached(Muscle::factory())
            ->hasAttached(Muscle::factory())
            ->create();

            $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME)
            ->get(route(self::MODEL_MAIN_ACTION_ROUTE, $variation));

        $response->assertSee($variation->muscles[0]->name);
        $response->assertSee($variation->muscles[1]->name);
        $response->assertSee($variation->muscles[2]->name);

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
}
