<?php

namespace Tests\Feature\Plans;

use App\Models\Plan;
use App\Models\User;
use Database\Seeders\permissionsSeeders\PlansPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IncludeMediasTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'plans';
    const MODEL_UPLOAD_FILE_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.uploadFiles';
    const MODEL_DOWNLOAD_FILE_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.downloadFile';

    const MODEL_FILES_MIME_TYPE = 'image/webp';
    const MODEL_FILES_COLLECTION_NAME = 'plans-images';
    const MODEL_FIEL_IMAGE_NAME_1 = 'plan-image1.webp';
    const MODEL_FIEL_IMAGE_NAME_2 = 'plan-image2.webp';

    protected User $user;
    protected string $token;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(PlansPermissionsSeeder::class);
        }

        [$this->user, $this->token] = $this->createUserWithToken();
    }

    /** @test */
    public function can_upload_single_image_to_plan(): void
    {
        $plan = Plan::factory()
            ->forGoal()->forFrequency()->forPhysicalCondition()
            ->create();

        $file = UploadedFile::fake()->image(self::MODEL_FIEL_IMAGE_NAME_1);

        $formData = $this->mediaUploadFormData(
            $plan,
            $file,
            self::MODEL_PLURAL_NAME,
            self::MODEL_FILES_COLLECTION_NAME
        );

        $response = $this->actingAs($this->user)
            ->withHeader('Authorization', $this->token)
            ->postJson(
                route(
                    self::MODEL_UPLOAD_FILE_ACTION_ROUTE,
                    $plan
                ),
                $formData
            );

        $response->assertStatus(200);

        // Check if the media was uploaded
        $plan->refresh();

        $this->assertCount(
            1,
            $plan->getMedia(self::MODEL_FILES_COLLECTION_NAME)
        );

        // Check the media details
        $media = $plan->getFirstMedia(self::MODEL_FILES_COLLECTION_NAME);

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
    public function can_upload_multiple_images_to_plan(): void
    {
        $plan = Plan::factory()
            ->forGoal()->forFrequency()->forPhysicalCondition()
            ->create();

        $files = [
            UploadedFile::fake()->image(self::MODEL_FIEL_IMAGE_NAME_1),
            UploadedFile::fake()->image(self::MODEL_FIEL_IMAGE_NAME_2),
        ];

        $formData = $this->mediaUploadFormData(
            $plan,
            $files,
            self::MODEL_PLURAL_NAME,
            self::MODEL_FILES_COLLECTION_NAME
        );

        $response = $this->actingAs($this->user)
            ->withHeader('Authorization', $this->token)
            ->postJson(
                route(
                    self::MODEL_UPLOAD_FILE_ACTION_ROUTE,
                    $plan
                ),
                $formData
            );

        $response->assertStatus(200);

        // Check if the media was uploaded
        $plan->refresh();

        $this->assertCount(
            2,
            $plan->getMedia(self::MODEL_FILES_COLLECTION_NAME)
        );

        // Check the media details
        foreach ($plan->getMedia(self::MODEL_FILES_COLLECTION_NAME) as $media) {
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
    public function upload_to_plans_clears_existing_media_collection(): void
    {
        $plan = Plan::factory()
            ->forGoal()->forFrequency()->forPhysicalCondition()
            ->create();

        // Add an initial media file
        $initialFile = UploadedFile::fake()->image('initial-image.webp');
        $plan->addMedia($initialFile)->toMediaCollection(self::MODEL_FILES_COLLECTION_NAME);

        // Check that the initial media exists
        $this->assertCount(
            1,
            $plan->getMedia(self::MODEL_FILES_COLLECTION_NAME)
        );

        // Upload new files
        $newFiles = [
            UploadedFile::fake()->image(self::MODEL_FIEL_IMAGE_NAME_1),
            UploadedFile::fake()->image(self::MODEL_FIEL_IMAGE_NAME_2),
        ];

        $formData = $this->mediaUploadFormData(
            $plan,
            $newFiles,
            self::MODEL_PLURAL_NAME,
            self::MODEL_FILES_COLLECTION_NAME
        );

        $response = $this->actingAs($this->user)
            ->withHeader('Authorization', $this->token)
            ->postJson(
                route(
                    self::MODEL_UPLOAD_FILE_ACTION_ROUTE,
                    $plan
                ),
                $formData
            );

        $response->assertStatus(200);

        // Check that the previous media collection was cleared and new media was added
        $plan->refresh();

        $this->assertCount(
            2,
            $plan->getMedia(self::MODEL_FILES_COLLECTION_NAME)
        );
    }

    /** @test */
    public function cannot_upload_files_to_plan_without_authentication(): void
    {
        $plan = Plan::factory()
            ->forGoal()->forFrequency()->forPhysicalCondition()
            ->create();

        $file = UploadedFile::fake()->image(self::MODEL_FIEL_IMAGE_NAME_1);

        $formData = $this->mediaUploadFormData(
            $plan,
            $file,
            self::MODEL_PLURAL_NAME,
            self::MODEL_FILES_COLLECTION_NAME
        );

        $response = $this->postJson(
            route(
                self::MODEL_UPLOAD_FILE_ACTION_ROUTE,
                $plan
            ),
            $formData
        );

        $response->assertStatus(401); // Unauthorized
    }

    /** @test */
    public function can_download_single_image_from_plan(): void
    {
        $plan = Plan::factory()
            ->forGoal()->forFrequency()->forPhysicalCondition()
            ->create();

        $file = UploadedFile::fake()->image(self::MODEL_FIEL_IMAGE_NAME_1);
        $plan->addMedia($file)->toMediaCollection(self::MODEL_FILES_COLLECTION_NAME);

        $media = $plan->getFirstMedia(self::MODEL_FILES_COLLECTION_NAME);

        $response = $this->actingAs($this->user)->jsonApi()
            ->withHeader('Authorization', $this->token)
            ->get(
                route(
                    self::MODEL_DOWNLOAD_FILE_ACTION_ROUTE,
                    [
                        'id' => $plan->id,
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
    public function download_returns_404_for_non_existent_plan_media(): void
    {
        $plan = Plan::factory()
            ->forGoal()->forFrequency()->forPhysicalCondition()
            ->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->withHeader('Authorization', $this->token)
            ->get(
                route(
                    self::MODEL_DOWNLOAD_FILE_ACTION_ROUTE,
                    [
                        'id' => $plan->id,
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
    public function download_returns_400_for_non_provided_plan_collection(): void
    {
        $plan = Plan::factory()
            ->forGoal()->forFrequency()->forPhysicalCondition()
            ->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->withHeader('Authorization', $this->token)
            ->get(
                route(
                    self::MODEL_DOWNLOAD_FILE_ACTION_ROUTE,
                    [
                        'id' => $plan->id,
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
    public function download_fails_for_non_existent_plan(): void
    {
        $response = $this->actingAs($this->user)->jsonApi()
            ->withHeader('Authorization', $this->token)
            ->get(
                route(
                    self::MODEL_DOWNLOAD_FILE_ACTION_ROUTE,
                    [
                        'id' => 9999, // Non-existent plan ID
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
    public function plan_upload_handles_empty_files_array(): void
    {
        $plan = Plan::factory()
            ->forGoal()->forFrequency()->forPhysicalCondition()
            ->create();

        $formData = $this->mediaUploadFormData(
            $plan,
            [],
            self::MODEL_PLURAL_NAME,
            self::MODEL_FILES_COLLECTION_NAME
        );

        $response = $this->actingAs($this->user)
            ->withHeader('Authorization', $this->token)
            ->postJson(
                route(
                    self::MODEL_UPLOAD_FILE_ACTION_ROUTE,
                    $plan
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
