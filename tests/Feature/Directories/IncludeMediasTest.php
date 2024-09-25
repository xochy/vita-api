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

class IncludeMediasTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'directories';
    const MODEL_INCLUDE_RELATIONSHIP_NAME = 'medias';
    const MODEL_SHOW_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';
    const MODEL_SHOW_RELATIONSHIP_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.medias';

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
    public function directories_can_include_medias()
    {
        $file1 = UploadedFile::fake()->image(Str::uuid()->toString() . '.jpg');
        $file2 = UploadedFile::fake()->image(Str::uuid()->toString() . '.jpg');
        $file3 = UploadedFile::fake()->image(Str::uuid()->toString() . '.jpg');

        $directory = Directory::factory()->create();

        $directory->addMedia($file1)->toMediaCollection();
        $directory->addMedia($file2)->toMediaCollection();
        $directory->addMedia($file3)->toMediaCollection();

        $response = $this->actingAs($this->user)->jsonApi()
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_NAME)
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $directory));

        $mediaItems = $directory->getMedia();

        $response->assertSee($mediaItems[0]->id);
        $response->assertSee($mediaItems[0]->id);
        $response->assertSee($mediaItems[0]->id);

        $response->assertJsonFragment(
            [
                'related' => route(self::MODEL_RELATED_ROUTE, $directory)
            ]
        );

        $response->assertJsonFragment(
            [
                'self' => route(self::MODEL_SELF_ROUTE, $directory)
            ]
        );
    }

    /** @test */
    public function directories_can_fetch_related_medias()
    {
        $file1 = UploadedFile::fake()->image(Str::uuid()->toString() . '.jpg');
        $file2 = UploadedFile::fake()->image(Str::uuid()->toString() . '.jpg');
        $file3 = UploadedFile::fake()->image(Str::uuid()->toString() . '.jpg');

        $directory = Directory::factory()->create();

        $directory->addMedia($file1)->toMediaCollection();
        $directory->addMedia($file2)->toMediaCollection();
        $directory->addMedia($file3)->toMediaCollection();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects('medias')
            ->get(route(self::MODEL_SHOW_RELATIONSHIP_ROUTE, $directory));

        $response->assertJsonCount(3, 'data');

        $mediaItems = $directory->getMedia();

        foreach ($mediaItems as $index => $mediaItem) {
            $response->assertJsonPath("data.$index.attributes.name", (string) $mediaItem->name);
            $response->assertJsonPath("data.$index.attributes.type", (string) $mediaItem->type);
            $response->assertJsonPath("data.$index.attributes.fielName", (string) $mediaItem->file_name);
        }
    }
}
