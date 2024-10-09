<?php

namespace Tests\Feature\Medias;

use App\Models\Directory;
use App\Models\User;
use Database\Seeders\PermissionsSeeders\DirectoriesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AddMediasToDirectoryTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'directories';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.update';
    const IMAGE_MIME_TYPE = 'image/jpeg';
    const IMAGE_PATH = 'root/';

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
    public function authenticated_users_can_add_medias_to_directory()
    {
        $file1 = UploadedFile::fake()->image($fileName1 = Str::uuid()->toString() . '.jpg');
        $file2 = UploadedFile::fake()->image($fileName2 = Str::uuid()->toString() . '.jpg');
        $file3 = UploadedFile::fake()->image($fileName3 = Str::uuid()->toString() . '.jpg');

        $directory = Directory::factory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $directory->getRouteKey(),
            'meta' => [
                'media' => [
                    'action' => 'store',
                    'path' => self::IMAGE_PATH,
                    'data' => [$file1, $file2, $file3]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $directory));

        $response->assertStatus(200);

        $this->assertFileExists(storage_path("app/public/1/{$fileName1}"));
        $this->assertFileExists(storage_path("app/public/2/{$fileName2}"));
        $this->assertFileExists(storage_path("app/public/3/{$fileName3}"));
    }

    /** @test */
    public function action_field_is_required()
    {
        $file1 = UploadedFile::fake()->image(Str::uuid()->toString() . '.jpg');

        $directory = Directory::factory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $directory->getRouteKey(),
            'meta' => [
                'media' => [
                    'path' => self::IMAGE_PATH,
                    'data' => [$file1]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $directory));

        $response->assertError(
            400,
            [
                'detail' => 'The action field is required.'
            ]
        );
    }

    /** @test */
    public function action_field_must_be_a_string()
    {
        $file1 = UploadedFile::fake()->image(Str::uuid()->toString() . '.jpg');

        $directory = Directory::factory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $directory->getRouteKey(),
            'meta' => [
                'media' => [
                    'action' => 1,
                    'path' => self::IMAGE_PATH,
                    'data' => [$file1]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $directory));

        $response->assertError(
            400,
            [
                'detail' => 'The action field must be a string.'
            ]
        );
    }

    /** @test */
    public function action_field_must_not_exceed_6_characters()
    {
        $file1 = UploadedFile::fake()->image(Str::uuid()->toString() . '.jpg');

        $directory = Directory::factory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $directory->getRouteKey(),
            'meta' => [
                'media' => [
                    'action' => Str::random(7),
                    'path' => self::IMAGE_PATH,
                    'data' => [$file1]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $directory));

        $response->assertError(
            400,
            [
                'detail' => 'The action field must not be greater than 6 characters.'
            ]
        );
    }

    /** @test */
    public function action_field_must_be_in_store_update_or_delete()
    {
        $file1 = UploadedFile::fake()->image(Str::uuid()->toString() . '.jpg');

        $directory = Directory::factory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $directory->getRouteKey(),
            'meta' => [
                'media' => [
                    'action' => 'wrong',
                    'path' => self::IMAGE_PATH,
                    'data' => [$file1]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $directory));

        $response->assertError(
            400,
            [
                'detail' => 'The selected action is invalid.'
            ]
        );
    }

    /** @test */
    public function path_field_is_required()
    {
        $file1 = UploadedFile::fake()->image(Str::uuid()->toString() . '.jpg');

        $directory = Directory::factory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $directory->getRouteKey(),
            'meta' => [
                'media' => [
                    'action' => 'store',
                    'data' => [$file1]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $directory));

        $response->assertError(
            400,
            [
                'detail' => 'The path field is required.'
            ]
        );
    }

    /** @test */
    public function path_field_must_be_a_string()
    {
        $file1 = UploadedFile::fake()->image(Str::uuid()->toString() . '.jpg');

        $directory = Directory::factory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $directory->getRouteKey(),
            'meta' => [
                'media' => [
                    'action' => 'store',
                    'path' => 1,
                    'data' => [$file1]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $directory));

        $response->assertError(
            400,
            [
                'detail' => 'The path field must be a string.'
            ]
        );
    }

    /** @test */
    public function base_64_content_field_is_required()
    {
        $directory = Directory::factory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $directory->getRouteKey(),
            'meta' => [
                'media' => [
                    'action' => 'store',
                    'path' => self::IMAGE_PATH,
                    'data' => [
                        [
                            'filename' => Str::uuid()->toString() . '.jpg'
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $directory));

        $response->assertError(
            400,
            [
                'detail' => "The content field is required."
            ]
        );
    }

    /** @test */
    public function base_64_content_field_must_be_a_string()
    {
        $directory = Directory::factory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $directory->getRouteKey(),
            'meta' => [
                'media' => [
                    'action' => 'store',
                    'path' => self::IMAGE_PATH,
                    'data' => [
                        [
                            'content' => 1,
                            'filename' => Str::uuid()->toString() . '.jpg'
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $directory));

        $response->assertError(
            400,
            [
                'detail' => 'The content field must be a string.'
            ]
        );
    }

    /** @test */
    public function base_64_filename_field_is_required()
    {
        $directory = Directory::factory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $directory->getRouteKey(),
            'meta' => [
                'media' => [
                    'action' => 'store',
                    'path' => self::IMAGE_PATH,
                    'data' => [
                        [
                            'content' => base64_encode('content')
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $directory));

        $response->assertError(
            400,
            [
                'detail' => 'The file name field is required.'
            ]
        );
    }

    /** @test */
    public function base_64_filename_field_must_be_a_string()
    {
        $directory = Directory::factory()->create();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $directory->getRouteKey(),
            'meta' => [
                'media' => [
                    'action' => 'store',
                    'path' => self::IMAGE_PATH,
                    'data' => [
                        [
                            'content' => base64_encode('content'),
                            'filename' => 1
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $directory));

        $response->assertError(
            400,
            [
                'detail' => 'The file name field must be a string.'
            ]
        );
    }
}
