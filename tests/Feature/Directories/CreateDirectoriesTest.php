<?php

namespace Tests\Feature\Directories;

use App\Models\Directory;
use App\Models\User;
use Database\Seeders\PermissionsSeeders\DirectoriesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CreateDirectoriesTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'directories';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.store';

    const MODEL_ATTRIBUTE_NAME = 'name';
    const MODEL_ATTRIBUTE_NAME_POINTER = '/data/attributes/name';
    const MODEL_ATTRIBUTE_NAME_POINTER_ASSERTION = 'data\/attributes\/name';

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
    public function guests_users_cannot_create_directories()
    {
        $directory = array_filter(Directory::factory()->raw());

        $response = $this->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withData(
                [
                    'type' => self::MODEL_PLURAL_NAME,
                    'attributes' => $directory
                ]
            )
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unauthorized (401)
        $response->assertStatus(401);

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $directory);
    }

    /** @test */
    public function authenticated_users_can_create_directories_without_parent_id()
    {
        $directory = array_filter(Directory::factory()->raw());

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withData(
                [
                    'type' => self::MODEL_PLURAL_NAME,
                    'attributes' => $directory
                ]
            )
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        $response->assertCreated();

        $this->assertDatabaseHas(self::MODEL_PLURAL_NAME, $directory);
    }

    /** @test */
    public function authenticated_users_can_create_directories_with_parent_id()
    {
        $parent = Directory::factory()->create();
        $directory = array_filter(Directory::factory()->raw());

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withData(
                [
                    'type' => self::MODEL_PLURAL_NAME,
                    'attributes' => $directory,
                    'relationships' => [
                        'parent' => [
                            'data' => [
                                'type' => 'directories',
                                'id' => (string) $parent->id
                            ]
                        ]
                    ]
                ]
            )
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        $response->assertCreated();

        $this->assertDatabaseHas(
            self::MODEL_PLURAL_NAME,
            [
                'name' => $directory['name'],
                'parent_id' => $parent->id
            ]
        );
    }
}
