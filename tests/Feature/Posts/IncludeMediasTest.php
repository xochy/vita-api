<?php

namespace Tests\Feature\Posts;

use App\Models\Post;
use App\Models\User;
use Database\Seeders\permissionsSeeders\PostsPermissionsSeeder;
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

    const MODEL_FILES_MIME_TYPE = 'image/webp';
    const MODEL_FILES_COLLECTION_NAME = 'posts-images';
    const MODEL_FIEL_IMAGE_NAME_1 = 'post-image1.webp';
    const MODEL_FIEL_IMAGE_NAME_2 = 'post-image2.webp';

    protected User $user;
    protected string $token;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(PostsPermissionsSeeder::class);
        }

        [$this->user, $this->token] = $this->createUserWithToken();

        Storage::fake('public');
    }

    /** @test */
    public function can_upload_single_image_to_post(): void
    {
        $post = Post::factory()->create(
            [
                'user_id' => $this->user->id,
            ]
        );

        $file = UploadedFile::fake()->image(self::MODEL_FIEL_IMAGE_NAME_1);

        $formData = $this->mediaUploadFormData(
            $post,
            $file,
            self::MODEL_PLURAL_NAME,
            self::MODEL_FILES_COLLECTION_NAME
        );

        $response = $this->actingAs($this->user)
            ->withHeader('Authorization', $this->token)
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

        $this->assertCount(
            1,
            $post->getMedia(self::MODEL_FILES_COLLECTION_NAME)
        );

        // Check the media details
        $media = $post->getFirstMedia(self::MODEL_FILES_COLLECTION_NAME);

        $this->assertEquals(
            self::MODEL_FIEL_IMAGE_NAME_1,
            $media->file_name
        );

        $this->assertEquals(
            self::MODEL_FILES_MIME_TYPE,
            $media->mime_type
        );
    }

    /** @test */
    public function can_upload_multiple_images_to_post(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $files = [
            UploadedFile::fake()->image(self::MODEL_FIEL_IMAGE_NAME_1),
            UploadedFile::fake()->image(self::MODEL_FIEL_IMAGE_NAME_2),
        ];

        $formData = $this->mediaUploadFormData(
            $post,
            $files,
            self::MODEL_PLURAL_NAME,
            self::MODEL_FILES_COLLECTION_NAME
        );

        $response = $this->actingAs($this->user)
            ->withHeader('Authorization', $this->token)
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

        $this->assertCount(
            2,
            $post->getMedia(self::MODEL_FILES_COLLECTION_NAME)
        );

        // Check the media details
        foreach ($post->getMedia(self::MODEL_FILES_COLLECTION_NAME) as $media) {
            $this->assertContains(
                $media->file_name,
                [self::MODEL_FIEL_IMAGE_NAME_1, self::MODEL_FIEL_IMAGE_NAME_2]
            );
            $this->assertEquals(
                self::MODEL_FILES_MIME_TYPE,
                $media->mime_type
            );
        }
    }

    /** @test */
    public function upload_to_posts_clears_existing_media_collection(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Add an initial media file
        $initialFile = UploadedFile::fake()->image('initial-image.webp');
        $post->addMedia($initialFile)->toMediaCollection(self::MODEL_FILES_COLLECTION_NAME);

        // Check that the initial media exists
        $this->assertCount(
            1,
            $post->getMedia(self::MODEL_FILES_COLLECTION_NAME)
        );

        // Upload new files
        $newFiles = [
            UploadedFile::fake()->image(self::MODEL_FIEL_IMAGE_NAME_1),
            UploadedFile::fake()->image(self::MODEL_FIEL_IMAGE_NAME_2),
        ];

        $formData = $this->mediaUploadFormData(
            $post,
            $newFiles,
            self::MODEL_PLURAL_NAME,
            self::MODEL_FILES_COLLECTION_NAME
        );

        $response = $this->actingAs($this->user)
            ->withHeader('Authorization', $this->token)
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

        $this->assertCount(
            2,
            $post->getMedia(self::MODEL_FILES_COLLECTION_NAME)
        );
    }

    /** @test */
    public function cannot_upload_files_to_post_without_authentication(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $file = UploadedFile::fake()->image(self::MODEL_FIEL_IMAGE_NAME_1);

        $formData = $this->mediaUploadFormData(
            $post,
            $file,
            self::MODEL_PLURAL_NAME,
            self::MODEL_FILES_COLLECTION_NAME
        );

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
    public function can_download_single_image_from_post(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $file = UploadedFile::fake()->image(self::MODEL_FIEL_IMAGE_NAME_1);
        $post->addMedia($file)->toMediaCollection(self::MODEL_FILES_COLLECTION_NAME);

        $media = $post->getFirstMedia(self::MODEL_FILES_COLLECTION_NAME);

        $response = $this->actingAs($this->user)->jsonApi()
            ->withHeader('Authorization', $this->token)
            ->get(
                route(
                    self::MODEL_DOWNLOAD_FILE_ACTION_ROUTE,
                    [
                        'id' => $post->id,
                        'mediaId' => $media->uuid,
                        'collection' => self::MODEL_FILES_COLLECTION_NAME,
                    ]
                )
            );

        $response->assertStatus(200);

        $response->assertHeader(
            'Content-Type',
            self::MODEL_FILES_MIME_TYPE
        );

        $response->assertHeader(
            'Content-Disposition',
            'inline; filename="' . self::MODEL_FIEL_IMAGE_NAME_1 . '"'
        );
    }

    /** @test */
    public function download_returns_404_for_non_existent_post_media(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->jsonApi()
            ->withHeader('Authorization', $this->token)
            ->get(
                route(
                    self::MODEL_DOWNLOAD_FILE_ACTION_ROUTE,
                    [
                        'id' => $post->id,
                        'mediaId' => 'non-existent-media-id',
                        'collection' => self::MODEL_FILES_COLLECTION_NAME,
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
    public function download_returns_400_for_non_provided_post_collection(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->jsonApi()
            ->withHeader('Authorization', $this->token)
            ->get(
                route(
                    self::MODEL_DOWNLOAD_FILE_ACTION_ROUTE,
                    [
                        'id' => $post->id,
                        'mediaId' => 'some-media-id'
                    ]
                )
            );

        // Wrong request
        $response->assertError(
            400,
            [
                'detail' => __('exceptions.collection_not_provided'),
            ]
        );
    }

    /** @test */
    public function download_fails_for_non_existent_post(): void
    {
        $response = $this->actingAs($this->user)->jsonApi()
            ->withHeader('Authorization', $this->token)
            ->get(
                route(
                    self::MODEL_DOWNLOAD_FILE_ACTION_ROUTE,
                    [
                        'id' => 9999, // Non-existent post ID
                        'mediaId' => 'some-media-id',
                        'collection' => self::MODEL_FILES_COLLECTION_NAME,
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
    public function post_upload_handles_empty_files_array(): void
    {
        $post = Post::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $formData = $this->mediaUploadFormData(
            $post,
            [],
            self::MODEL_PLURAL_NAME,
            self::MODEL_FILES_COLLECTION_NAME
        );

        $response = $this->actingAs($this->user)
            ->withHeader('Authorization', $this->token)
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
