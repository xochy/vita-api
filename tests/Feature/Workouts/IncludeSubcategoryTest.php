<?php

namespace Tests\Feature\Workouts;

use App\Models\Subcategory;
use App\Models\User;
use App\Models\Workout;
use Database\Seeders\permissionsSeeders\SubcategoriesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IncludeSubcategoryTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'workouts';
    const MODEL_INCLUDE_RELATIONSHIP_NAME = 'subcategory';
    const MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME = 'subcategories';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';

    const MODEL_RELATED_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME
        . '.' . self::MODEL_INCLUDE_RELATIONSHIP_NAME;

    const MODEL_SELF_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME
        . '.' . self::MODEL_INCLUDE_RELATIONSHIP_NAME . '.show';

    protected User $user;
    protected Subcategory $subcategory;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(SubcategoriesPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
        $this->subcategory = Subcategory::factory()->forCategory()->create();
    }

    /** @test */
    public function workouts_can_include_subcategory()
    {
        $workout = Workout::factory()->for($this->subcategory)->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_NAME)
            ->get(route(self::MODEL_MAIN_ACTION_ROUTE, $workout));

        $response->assertSee($workout->subcategory->getRouteKey());

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
    public function workouts_can_fetch_related_subcategory()
    {
        $workout = Workout::factory()->for($this->subcategory)->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME)
            ->get(route(self::MODEL_SELF_ROUTE, $workout));

        $response->assertFetchedOne($workout->subcategory);
    }
}
