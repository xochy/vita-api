<?php

namespace Tests\Feature\Directories;

use App\Models\Directory;
use App\Models\User;
use Database\Seeders\PermissionsSeeders\DirectoriesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IncludeDirectoriesTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'directories';
    const MODEL_INCLUDE_RELATIONSHIP_NAME = 'subdirectories';
    const MODEL_SHOW_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';
    const MODEL_SHOW_RELATIONSHIP_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.subdirectories';

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
            $this->seed(DirectoriesPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function directories_can_include_subdirectories()
    {
        $parentDirectory = Directory::factory()->create();
        Directory::factory(
            [
                'parent_id' => $parentDirectory->id,
            ]
        )->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_NAME)
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $parentDirectory));

        $response->assertSee($parentDirectory->children[0]->slug);

        $response->assertJsonFragment(
            [
                'related' => route(self::MODEL_RELATED_ROUTE, $parentDirectory)
            ]
        );

        $response->assertJsonFragment(
            [
                'self' => route(self::MODEL_SELF_ROUTE, $parentDirectory)
            ]
        );
    }

    /** @test */
    public function directories_can_fetch_related_subdirectories()
    {
        $parentDirectory = Directory::factory()->create();

        Directory::factory()->count(3)
            ->state(
                [
                    'parent_id' => $parentDirectory->id,
                ]
            )
            ->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->get(route(self::MODEL_SHOW_RELATIONSHIP_ROUTE, $parentDirectory));

        $response->assertJsonCount(3, 'data');

        foreach ($parentDirectory->children as $child) {
            $response->assertJsonFragment(
                [
                    'id' => (string) $child->getRouteKey(),
                    'name' => $child->name,
                ]
            );
        }
    }


    /** @test */
    public function directories_can_include_parent_directory()
    {
        $parentDirectory = Directory::factory()->create();
        $childDirectory = Directory::factory(
            [
                'parent_id' => $parentDirectory->id,
            ]
        )->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->includePaths('parent')
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $childDirectory));

        $response->assertSee($childDirectory->parent->slug);

        $response->assertJsonFragment(
            [
                'related' => route(self::MODEL_RELATED_ROUTE, $childDirectory)
            ]
        );

        $response->assertJsonFragment(
            [
                'self' => route(self::MODEL_SELF_ROUTE, $childDirectory)
            ]
        );
    }
}
