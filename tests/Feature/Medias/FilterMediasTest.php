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

class FilterMediasTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_SINGLE_NAME = 'media';
    const MODEL_PLURAL_NAME = 'medias';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.index';

    const MODEL_FILTER_NAME_PARAM_NAME = 'filter[name]';
    const MODEL_FILTER_SEARCH_PARAM_NAME = 'filter[search]';

    const IMAGE_1_NAME = 'clean beach';
    const IMAGE_2_NAME = 'desert';
    const IMAGE_3_NAME = 'profile in beach';
    const IMAGE_4_NAME = 'mountain';
    const IMAGE_5_NAME = 'desktop';
    const IMAGE_6_NAME = 'desert wallpaper';
    const IMAGE_EXTENSION = '.jpg';

    protected User $user;
    protected Directory $root;
    protected Directory $images;
    protected Directory $screenshots;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(DirectoriesPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('superAdmin');

        $this->root = Directory::factory()->create();

        $this->images = Directory::factory()->create(
            [
                'name' => 'Images',
                'parent_id' => $this->root->id,
            ]
        );

        $this->screenshots = Directory::factory()->create(
            [
                'name' => 'Screenshots',
                'parent_id' => $this->images->id,
            ]
        );

        $rootImage1 = UploadedFile::fake()->image(self::IMAGE_1_NAME . self::IMAGE_EXTENSION);
        $rootImage2 = UploadedFile::fake()->image(self::IMAGE_2_NAME . self::IMAGE_EXTENSION);

        $this->root->addMedia($rootImage1)->toMediaCollection('files');
        $this->root->addMedia($rootImage2)->toMediaCollection('files');

        $imagesImage1 = UploadedFile::fake()->image(self::IMAGE_3_NAME . self::IMAGE_EXTENSION);
        $imagesImage2 = UploadedFile::fake()->image(self::IMAGE_4_NAME . self::IMAGE_EXTENSION);

        $this->images->addMedia($imagesImage1)->toMediaCollection('files');
        $this->images->addMedia($imagesImage2)->toMediaCollection('files');

        $screenshotsImage1 = UploadedFile::fake()->image(self::IMAGE_5_NAME . self::IMAGE_EXTENSION);
        $screenshotsImage2 = UploadedFile::fake()->image(self::IMAGE_6_NAME . self::IMAGE_EXTENSION);

        $this->screenshots->addMedia($screenshotsImage1)->toMediaCollection('files');
        $this->screenshots->addMedia($screenshotsImage2)->toMediaCollection('files');
    }

    /** @test */
    public function can_fliter_medias_by_name()
    {
        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                self::MODEL_FILTER_NAME_PARAM_NAME => self::IMAGE_1_NAME
            ]
        );

        $this->actingAs($this->user)->jsonApi()->get($url)
            ->assertJsonCount(1, 'data')
            ->assertSee(self::IMAGE_1_NAME)
            ->assertDontSee(self::IMAGE_2_NAME)
            ->assertDontSee(self::IMAGE_3_NAME)
            ->assertDontSee(self::IMAGE_4_NAME)
            ->assertDontSee(self::IMAGE_5_NAME)
            ->assertDontSee(self::IMAGE_6_NAME);
    }

    /** @test */
    public function can_filter_medias_by_search_term()
    {
        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                self::MODEL_FILTER_SEARCH_PARAM_NAME => 'beach'
            ]
        );

        $this->actingAs($this->user)->jsonApi()->get($url)
            ->assertJsonCount(2, 'data')
            ->assertSee(self::IMAGE_1_NAME)
            ->assertSee(self::IMAGE_3_NAME)
            ->assertDontSee(self::IMAGE_2_NAME)
            ->assertDontSee(self::IMAGE_4_NAME)
            ->assertDontSee(self::IMAGE_5_NAME)
            ->assertDontSee(self::IMAGE_6_NAME);
    }
}
