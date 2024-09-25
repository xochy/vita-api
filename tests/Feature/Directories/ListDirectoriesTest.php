<?php

namespace Tests\Feature\Directories;

use App\Models\Directory;
use App\Models\User;
use Database\Seeders\PermissionsSeeders\DirectoriesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ListDirectoriesTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'directories';
    const MODEL_SHOW_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';
    const MODEL_INDEX_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.index';

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
    public function it_can_fetch_single_directory()
    {
        $directory = Directory::factory()->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $directory));

        $response->assertFetchedOne(
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $directory->getRouteKey(),
                'attributes' => [
                    'name' => $directory->name,
                    'slug' => $directory->slug,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $directory)
                ]
            ]
        );
    }

    /** @test */
    public function it_can_fetch_all_directories()
    {
        $directories = Directory::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->get(route(self::MODEL_INDEX_ACTION_ROUTE));

        $response->assertFetchedMany(
            $directories->map(function (Directory $directory) {
                return [
                    'type' => self::MODEL_PLURAL_NAME,
                    'id' => (string) $directory->getRouteKey(),
                    'attributes' => [
                        'name' => $directory->name,
                        'slug' => $directory->slug,
                    ],
                    'links' => [
                        'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $directory)
                    ]
                ];
            })->all()
        );
    }
}
