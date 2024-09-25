<?php

namespace Tests\Feature\Medias;

use App\Models\Directory;
use App\Models\User;
use Database\Seeders\PermissionsSeeders\DirectoriesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UpdateMediasTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'directories';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.update';
    const MODEL_SHOW_RELATIONSHIP_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.medias';
    const FILE_NEW_NAME = 'NewName.jpg';
    const FILE_MIME_TYPE = 'image/jpeg';
    const FILE_ROUTE_PATH = 'app/public/1/';
    const IMAGE_PATH = 'root/';

    protected User $user;
    protected string $fileName;
    protected Directory $directory;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(DirectoriesPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');

        $file1 = UploadedFile::fake()->create($this->fileName = 'file1.jpg', 500, self::FILE_MIME_TYPE);

        $this->directory = Directory::factory()->create();
        $this->directory->addMedia($file1)->toMediaCollection($this->directory->id);
    }

    /** @test */
    public function authenticated_users_can_rename_medias()
    {
        $this->assertFileExists(storage_path(self::FILE_ROUTE_PATH . $this->fileName));

        $media = $this->directory->getMedia($this->directory->id)->first();
        $this->assertEquals($this->fileName, $media->file_name);

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $this->directory->getRouteKey(),
            'meta' => [
                'media' => [
                    'action' => 'update',
                    'path' => self::IMAGE_PATH,
                    'data' => [
                        [
                            'id' => (string) $media->id,
                            'attributes' => [
                                'filename' => self::FILE_NEW_NAME,
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $this->directory));

        $response->assertStatus(200);

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects('medias')
            ->get(route(self::MODEL_SHOW_RELATIONSHIP_ROUTE, $this->directory));

        $response->assertStatus(200);

        $response->assertJsonFragment(
            [
                'name' => self::FILE_NEW_NAME
            ]
        );

        $this->assertFileExists(storage_path(self::FILE_ROUTE_PATH . self::FILE_NEW_NAME));
    }

    /** @test */
    public function id_field_is_required()
    {
        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $this->directory->getRouteKey(),
            'meta' => [
                'media' => [
                    'action' => 'update',
                    'path' => self::IMAGE_PATH,
                    'data' => [
                        [
                            'attributes' => [
                                'filename' => self::FILE_NEW_NAME,
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $this->directory));

        $response->assertError(
            400,
            [
                'detail' => 'The id field is required.'
            ]
        );
    }

    /** @test */
    public function id_field_must_be_a_string()
    {
        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $this->directory->getRouteKey(),
            'meta' => [
                'media' => [
                    'action' => 'update',
                    'path' => self::IMAGE_PATH,
                    'data' => [
                        [
                            'id' => 999,
                            'attributes' => [
                                'filename' => self::FILE_NEW_NAME,
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $this->directory));

        $response->assertError(
            400,
            [
                'detail' => 'The id field must be a string.'
            ]
        );
    }

    /** @test */
    public function id_field_must_exist_in_media_table()
    {
        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $this->directory->getRouteKey(),
            'meta' => [
                'media' => [
                    'action' => 'update',
                    'path' => self::IMAGE_PATH,
                    'data' => [
                        [
                            'id' => '999',
                            'attributes' => [
                                'filename' => self::FILE_NEW_NAME,
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $this->directory));

        $response->assertError(
            400,
            [
                'detail' => 'The selected id is invalid.'
            ]
        );
    }

    /** @test */
    public function filename_field_is_required()
    {
        $media = $this->directory->getMedia($this->directory->id)->first();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $this->directory->getRouteKey(),
            'meta' => [
                'media' => [
                    'action' => 'update',
                    'path' => self::IMAGE_PATH,
                    'data' => [
                        [
                            'id' => (string) $media->id,
                            'attributes' => []
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $this->directory));

        $response->assertError(
            400,
            [
                'detail' => 'The file name field is required.'
            ]
        );
    }

    /** @test */
    public function filename_field_must_be_a_string()
    {
        $media = $this->directory->getMedia($this->directory->id)->first();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $this->directory->getRouteKey(),
            'meta' => [
                'media' => [
                    'action' => 'update',
                    'path' => self::IMAGE_PATH,
                    'data' => [
                        [
                            'id' => (string) $media->id,
                            'attributes' => [
                                'filename' => 999
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $this->directory));

        $response->assertError(
            400,
            [
                'detail' => 'The file name field must be a string.'
            ]
        );
    }

    /** @test */
    public function filename_field_must_have_a_maximum_of_255_characters()
    {
        $media = $this->directory->getMedia($this->directory->id)->first();

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'id' => (string) $this->directory->getRouteKey(),
            'meta' => [
                'media' => [
                    'action' => 'update',
                    'path' => self::IMAGE_PATH,
                    'data' => [
                        [
                            'id' => (string) $media->id,
                            'attributes' => [
                                'filename' => str_repeat('a', 256)
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->patch(route(self::MODEL_MAIN_ACTION_ROUTE, $this->directory));

        $response->assertError(
            400,
            [
                'detail' => 'The file name field must not be greater than 255 characters.'
            ]
        );
    }
}
