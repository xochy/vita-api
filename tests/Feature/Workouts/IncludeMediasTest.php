<?php

namespace Tests\Feature\Workouts;

use App\Models\User;
use App\Models\Workout;
use Database\Seeders\permissionsSeeders\WorkoutsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IncludeMediasTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'workouts';
    const MODEL_UPLOAD_FILE_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.uploadFiles';
    const MODEL_DOWNLOAD_FILE_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.downloadFile';

    const MODEL_FILES_MIME_TYPE = 'image/webp';
    const MODEL_FILES_COLLECTION_NAME = 'workouts-images';
    const MODEL_FIEL_IMAGE_NAME_1 = 'workout-image1.webp';
    const MODEL_FIEL_IMAGE_NAME_2 = 'workout-image2.webp';

    protected User $user;
    protected string $token;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(WorkoutsPermissionsSeeder::class);
        }

        [$this->user, $this->token] = $this->createUserWithToken();

        Storage::fake('public');
    }

    /** @test */
    public function can_upload_single_image_to_workout(): void
    {
        $workout = Workout::factory()->forCategory()->create();

        $file = UploadedFile::fake()->image(self::MODEL_FIEL_IMAGE_NAME_1);

        $formData = $this->mediaUploadFormData(
            $workout,
            $file,
            self::MODEL_PLURAL_NAME,
            self::MODEL_FILES_COLLECTION_NAME
        );

        $response = $this->actingAs($this->user)
            ->withHeader('Authorization', $this->token)
            ->postJson(
                route(
                    self::MODEL_UPLOAD_FILE_ACTION_ROUTE,
                    $workout
                ),
                $formData
            );

        $response->assertStatus(200);

        // Check if the media was uploaded
        $workout->refresh();

        $this->assertCount(
            1,
            $workout->getMedia(self::MODEL_FILES_COLLECTION_NAME)
        );

        // Check the media details
        $media = $workout->getFirstMedia(self::MODEL_FILES_COLLECTION_NAME);

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
    public function can_upload_multiple_images_to_workout(): void
    {
        $workout = Workout::factory()->forCategory()->create();

        $files = [
            UploadedFile::fake()->image(self::MODEL_FIEL_IMAGE_NAME_1),
            UploadedFile::fake()->image(self::MODEL_FIEL_IMAGE_NAME_2),
        ];

        $formData = $this->mediaUploadFormData(
            $workout,
            $files,
            self::MODEL_PLURAL_NAME,
            self::MODEL_FILES_COLLECTION_NAME
        );

        $response = $this->actingAs($this->user)
            ->withHeader('Authorization', $this->token)
            ->postJson(
                route(
                    self::MODEL_UPLOAD_FILE_ACTION_ROUTE,
                    $workout
                ),
                $formData
            );

        $response->assertStatus(200);

        // Check if the media was uploaded
        $workout->refresh();

        $this->assertCount(
            2,
            $workout->getMedia(self::MODEL_FILES_COLLECTION_NAME)
        );

        // Check the media details
        foreach ($workout->getMedia(self::MODEL_FILES_COLLECTION_NAME) as $media) {
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
    public function upload_to_workouts_clears_existing_media_collection(): void
    {
        $workout = Workout::factory()->forCategory()->create();

        // Add an initial media file
        $initialFile = UploadedFile::fake()->image('initial-image.webp');
        $workout->addMedia($initialFile)->toMediaCollection(self::MODEL_FILES_COLLECTION_NAME);

        // Check that the initial media exists
        $this->assertCount(
            1,
            $workout->getMedia(self::MODEL_FILES_COLLECTION_NAME)
        );

        // Upload new files
        $newFiles = [
            UploadedFile::fake()->image(self::MODEL_FIEL_IMAGE_NAME_1),
            UploadedFile::fake()->image(self::MODEL_FIEL_IMAGE_NAME_2),
        ];

        $formData = $this->mediaUploadFormData(
            $workout,
            $newFiles,
            self::MODEL_PLURAL_NAME,
            self::MODEL_FILES_COLLECTION_NAME
        );

        $response = $this->actingAs($this->user)
            ->withHeader('Authorization', $this->token)
            ->postJson(
                route(
                    self::MODEL_UPLOAD_FILE_ACTION_ROUTE,
                    $workout
                ),
                $formData
            );

        $response->assertStatus(200);

        // Check that the previous media collection was cleared and new media was added
        $workout->refresh();

        $this->assertCount(
            2,
            $workout->getMedia(self::MODEL_FILES_COLLECTION_NAME)
        );
    }

    /** @test */
    public function cannot_upload_files_to_workout_without_authentication(): void
    {
        $workout = Workout::factory()->forCategory()->create();

        $file = UploadedFile::fake()->image(self::MODEL_FIEL_IMAGE_NAME_1);

        $formData = $this->mediaUploadFormData(
            $workout,
            $file,
            self::MODEL_PLURAL_NAME,
            self::MODEL_FILES_COLLECTION_NAME
        );

        $response = $this->postJson(
            route(
                self::MODEL_UPLOAD_FILE_ACTION_ROUTE,
                $workout
            ),
            $formData
        );

        $response->assertStatus(401); // Unauthorized
    }

    /** @test */
    public function can_download_single_image_from_workout(): void
    {
        $workout = Workout::factory()->forCategory()->create();

        $file = UploadedFile::fake()->image(self::MODEL_FIEL_IMAGE_NAME_1);
        $workout->addMedia($file)->toMediaCollection(self::MODEL_FILES_COLLECTION_NAME);

        $media = $workout->getFirstMedia(self::MODEL_FILES_COLLECTION_NAME);

        $response = $this->actingAs($this->user)->jsonApi()
            ->withHeader('Authorization', $this->token)
            ->get(
                route(
                    self::MODEL_DOWNLOAD_FILE_ACTION_ROUTE,
                    [
                        'id' => $workout->id,
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
    public function download_returns_404_for_non_existent_workout_media(): void
    {
        $workout = Workout::factory()->forCategory()->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->withHeader('Authorization', $this->token)
            ->get(
                route(
                    self::MODEL_DOWNLOAD_FILE_ACTION_ROUTE,
                    [
                        'id' => $workout->id,
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
    public function download_returns_400_for_non_provided_workout_collection(): void
    {
        $workout = Workout::factory()->forCategory()->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->withHeader('Authorization', $this->token)
            ->get(
                route(
                    self::MODEL_DOWNLOAD_FILE_ACTION_ROUTE,
                    [
                        'id' => $workout->id,
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
    public function download_fails_for_non_existent_workout(): void
    {
        $response = $this->actingAs($this->user)->jsonApi()
            ->withHeader('Authorization', $this->token)
            ->get(
                route(
                    self::MODEL_DOWNLOAD_FILE_ACTION_ROUTE,
                    [
                        'id' => 9999, // Non-existent workout ID
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
    public function workout_upload_handles_empty_files_array(): void
    {
        $workout = Workout::factory()->forCategory()->create();

        $formData = $this->mediaUploadFormData(
            $workout,
            [],
            self::MODEL_PLURAL_NAME,
            self::MODEL_FILES_COLLECTION_NAME
        );

        $response = $this->actingAs($this->user)
            ->withHeader('Authorization', $this->token)
            ->postJson(
                route(
                    self::MODEL_UPLOAD_FILE_ACTION_ROUTE,
                    $workout
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
