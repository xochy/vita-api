<?php

namespace Tests\Feature\Posts;

use App\Models\Post;
use App\Models\User;
use Database\Seeders\permissionsSeeders\PostsPermissionsSeeders;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IncludeMediasTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'posts';
    const MODEL_UPLOAD_FILE_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.uploadFiles';
    const MODEL_DOWNLOAD_FILE_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.downloadFile';

    const MODEL_FILES_MIME_TYPE = 'image/jpeg';

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(PostsPermissionsSeeders::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');

        Storage::fake('public');
    }

    /** @test */
    public function can_upload_single_image_to_post()
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $file = UploadedFile::fake()->image('muscle-image.jpg', 800, 600);

        $formData = $this->mediaUploadFormData($post, $file, 'posts', 'images');

        $response = $this->actingAs($this->user)
            ->postJson(
                route(
                    self::MODEL_UPLOAD_FILE_ACTION_ROUTE,
                    $post
                ),
                $formData
            );

        $response->assertStatus(200);

        // Check if the media was uploaded
        $post->refresh();
        $this->assertCount(1, $post->getMedia('images'));

        // Check the media details
        $media = $post->getFirstMedia('images');
        $this->assertEquals('muscle-image.jpg', $media->file_name);
        $this->assertEquals(self::MODEL_FILES_MIME_TYPE, $media->mime_type);
    }

    /** @test */
    public function can_upload_multiple_images_to_post()
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $files = [
            UploadedFile::fake()->image('muscle-image1.jpg', 800, 600),
            UploadedFile::fake()->image('muscle-image2.jpg', 800, 600),
        ];

        $formData = $this->mediaUploadFormData($post, $files, 'posts', 'images');

        $response = $this->actingAs($this->user)
            ->postJson(
                route(
                    self::MODEL_UPLOAD_FILE_ACTION_ROUTE,
                    $post
                ),
                $formData
            );

        $response->assertStatus(200);

        // Check if the media was uploaded
        $post->refresh();
        $this->assertCount(2, $post->getMedia('images'));

        // Check the media details
        foreach ($post->getMedia('images') as $index => $media) {
            $this->assertEquals('muscle-image' . ($index + 1) . '.jpg', $media->file_name);
            $this->assertEquals(self::MODEL_FILES_MIME_TYPE, $media->mime_type);
        }
    }

    /** @test */
    public function upload_clears_existing_media_collection()
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Add an initial media file
        $initialFile = UploadedFile::fake()->image('initial-image.jpg', 800, 600);
        $post->addMedia($initialFile)->toMediaCollection('images');

        // Check that the initial media exists
        $this->assertCount(1, $post->getMedia('images'));

        // Upload new files
        $newFiles = [
            UploadedFile::fake()->image('new-image1.jpg', 800, 600),
            UploadedFile::fake()->image('new-image2.jpg', 800, 600),
        ];

        $formData = $this->mediaUploadFormData($post, $newFiles, 'posts', 'images');

        $response = $this->actingAs($this->user)
            ->postJson(
                route(
                    self::MODEL_UPLOAD_FILE_ACTION_ROUTE,
                    $post
                ),
                $formData
            );

        $response->assertStatus(200);

        // Check that the previous media collection was cleared and new media was added
        $post->refresh();
        $this->assertCount(2, $post->getMedia('images'));
    }

    /** @test */
    public function cannot_upload_files_to_post_without_authentication()
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $file = UploadedFile::fake()->image('muscle-image.jpg', 800, 600);

        $formData = $this->mediaUploadFormData($post, $file, 'posts', 'images');

        $response = $this->postJson(
            route(
                self::MODEL_UPLOAD_FILE_ACTION_ROUTE,
                $post
            ),
            $formData
        );

        $response->assertStatus(401); // Unauthorized
    }

    /** @test */
    public function can_download_single_image_from_post()
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $file = UploadedFile::fake()->image('muscle-image.jpg', 800, 600);
        $post->addMedia($file)->toMediaCollection('images');

        $media = $post->getFirstMedia('images');

        $response = $this->actingAs($this->user)
            ->jsonApi()
            ->get(
                route(
                    self::MODEL_DOWNLOAD_FILE_ACTION_ROUTE,
                    [
                        'id' => $post->id,
                        'mediaId' => $media->uuid,
                    ]
                )
            );

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', self::MODEL_FILES_MIME_TYPE);
        $response->assertHeader('Content-Disposition', 'inline; filename="muscle-image.jpg"');
    }

    /** @test */
    public function download_returns_404_for_non_existent_media()
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->jsonApi()
            ->get(
                route(
                    self::MODEL_DOWNLOAD_FILE_ACTION_ROUTE,
                    [
                        'id' => $post->id,
                        'mediaId' => 'non-existent-media-id',
                    ]
                )
            );

        // Not Found
        $response->assertError(
            404,
            [
                'detail' => __('exceptions.media_file_not_found'),
            ]
        );
    }

    /** @test */
    public function download_fails_for_non_existent_post()
    {
        $response = $this->actingAs($this->user)
            ->jsonApi()
            ->get(
                route(
                    self::MODEL_DOWNLOAD_FILE_ACTION_ROUTE,
                    [
                        'id' => 9999, // Non-existent post ID
                        'mediaId' => 'some-media-id',
                    ]
                )
            );

        // Not Found
        $response->assertError(
            404,
            [
                'detail' => __('exceptions.model_not_found'),
            ]
        );
    }

    /** @test */
    public function upload_handles_empty_files_array()
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $formData = $this->mediaUploadFormData($post, [], 'posts', 'images');

        $response = $this->actingAs($this->user)
            ->postJson(
                route(
                    self::MODEL_UPLOAD_FILE_ACTION_ROUTE,
                    $post
                ),
                $formData
            );

        // Expect a 400 Bad Request response
        $response->assertStatus(400);
        $response->assertJson([
            'errors' => [
                [
                    'status' => '400',
                    'detail' => "The current request does not have a file in a key named `files`",
                ],
            ],
        ]);
    }
}
