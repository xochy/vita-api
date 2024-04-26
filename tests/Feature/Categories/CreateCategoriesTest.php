<?php

namespace Tests\Feature\Categories;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use Database\Seeders\RoleSeeder;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\PermissionsSeeders\CategoriesPermissionsSeeder;

class CreateCategoriesTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'categories';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.store';

    const MODEL_ATTRIBUTE_NAME = 'name';
    const MODEL_ATTRIBUTE_DESCRIPTION = 'description';

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(CategoriesPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function guests_users_cannot_create_categories()
    {
        $category = array_filter(Category::factory()->raw());

        $response = $this->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withData([
                'type' => self::MODEL_PLURAL_NAME,
                'attributes' => $category
            ])
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unauthorized (401)
        $response->assertStatus(401);

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $category);
    }

    /** @test */
    public function authenticated_users_as_admin_can_create_categories()
    {
        $category = array_filter(Category::factory()->raw());

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $category
        ];

        $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE))
            ->assertCreated();

        $this->assertDatabaseHas(self::MODEL_PLURAL_NAME, [
            self::MODEL_ATTRIBUTE_NAME        => $category[self::MODEL_ATTRIBUTE_NAME],
            self::MODEL_ATTRIBUTE_DESCRIPTION => $category[self::MODEL_ATTRIBUTE_DESCRIPTION],
        ]);
    }

    /** @test */
    public function category_name_is_required()
    {
        $category = Category::factory()->raw(['name' => '']);

        $data = [
            'type'       => self::MODEL_PLURAL_NAME,
            'attributes' => $category
        ];

        $response = $this->actingAs($this->user)->jsonApi()
        ->expects(self::MODEL_PLURAL_NAME)->withData($data)
        ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(422, [
            'source' => ['pointer' => '/data/attributes/name'],
            'detail' => 'The name field is required.'
        ]);

        $response->assertSee('data\/attributes\/name');

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $category);
    }

    /** @test */
    public function category_name_must_be_unique()
    {
        $category = Category::factory()->create();

        $data = [
            'type'       => self::MODEL_PLURAL_NAME,
            'attributes' => array_filter(Category::factory()->raw([
                'name' => $category->name
            ]))
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(422, [
            'source' => ['pointer' => '/data/attributes/name'],
            'detail' => 'The name has already been taken.'
        ]);

        $response->assertSee('data\/attributes\/name');

        $this->assertDatabaseCount(self::MODEL_PLURAL_NAME, 1);
    }

    /** @test */
    public function category_description_is_required()
    {
        $category = Category::factory()->raw([self::MODEL_ATTRIBUTE_DESCRIPTION => '']);

        $data = [
            'type'       => self::MODEL_PLURAL_NAME,
            'attributes' => $category
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(422, [
            'source' => ['pointer' => '/data/attributes/description'],
            'detail' => 'The description field is required.'
        ]);

        $response->assertSee('data\/attributes\/description');

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $category);
    }

    /** @test */
    public function category_description_must_be_a_string()
    {
        $category = Category::factory()->raw([self::MODEL_ATTRIBUTE_DESCRIPTION => 123]);

        $data = [
            'type'       => self::MODEL_PLURAL_NAME,
            'attributes' => $category
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(422, [
            'source' => ['pointer' => '/data/attributes/description'],
            'detail' => 'The description field must be a string.'
        ]);

        $response->assertSee('data\/attributes\/description');

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $category);
    }
}
