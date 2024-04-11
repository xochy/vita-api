<?php

namespace Tests\Feature\Subcategories;

use App\Models\Category;
use App\Models\Subcategory;
use App\Models\User;
use Database\Seeders\permissionsSeeders\SubcategoriesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CreateSubcategoriesTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'subcategories';
    const MODEL_INCLUDE_RELATIONSHIP_SINGLE_NAME = 'category';
    const MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME = 'categories';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.store';

    const MODEL_ATTRIBUTE_NAME = 'name';
    const MODEL_ATTRIBUTE_DESCRIPTION = 'description';

    protected User $user;
    protected Category $category;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(SubcategoriesPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
        $this->category = Category::factory()->create();
    }

    /** @test */
    public function guests_users_cannot_create_subcategories()
    {
        $subcategory = array_filter(Subcategory::factory()->raw());

        $response = $this->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withData([
                'type' => self::MODEL_PLURAL_NAME,
                'attributes' => $subcategory
            ])
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unauthorized (401)
        $response->assertStatus(401);

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $subcategory);
    }

    /** @test */
    public function authenticated_users_as_admin_can_create_subcategories()
    {
        $subcategory = array_filter(Subcategory::factory()->raw());

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $subcategory);

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $subcategory,
            'relationships' => [
                self::MODEL_INCLUDE_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->category->getRouteKey()
                    ]
                ]
            ]
        ];

        $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_SINGLE_NAME)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE))
            ->assertCreated();

        $this->assertDatabaseHas(self::MODEL_PLURAL_NAME, [
            self::MODEL_ATTRIBUTE_NAME        => $subcategory[self::MODEL_ATTRIBUTE_NAME],
            self::MODEL_ATTRIBUTE_DESCRIPTION => $subcategory[self::MODEL_ATTRIBUTE_DESCRIPTION],
        ]);
    }

    /** @test */
    public function authenticated_users_as_user_cannot_create_subcategories()
    {
        $subcategory = array_filter(Subcategory::factory()->raw());

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $subcategory,
            'relationships' => [
                self::MODEL_INCLUDE_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->category->getRouteKey()
                    ]
                ]
            ]
        ];

        $user = User::factory()->create()->assignRole('user');

        $response = $this->actingAs($user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_SINGLE_NAME)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Forbidden (403)
        $response->assertStatus(403);

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, [
            self::MODEL_ATTRIBUTE_NAME        => $subcategory[self::MODEL_ATTRIBUTE_NAME],
            self::MODEL_ATTRIBUTE_DESCRIPTION => $subcategory[self::MODEL_ATTRIBUTE_DESCRIPTION],
        ]);
    }

    /** @test */
    public function subcategory_name_is_required()
    {
        $subcategory = Subcategory::factory()->raw([self::MODEL_ATTRIBUTE_NAME => '']);

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $subcategory,
            'relationships' => [
                self::MODEL_INCLUDE_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->category->getRouteKey()
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_SINGLE_NAME)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(422, [
            'source' => ['pointer' => '/data/attributes/name'],
            'detail' => 'The name field is required.'
        ]);

        $response->assertSee('data\/attributes\/name');

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $subcategory);
    }

    /** @test */
    public function subcategory_description_is_required()
    {
        $subcategory = Subcategory::factory()->raw([self::MODEL_ATTRIBUTE_DESCRIPTION => '']);

        $data = [
            'type' => self::MODEL_PLURAL_NAME,
            'attributes' => $subcategory,
            'relationships' => [
                self::MODEL_INCLUDE_RELATIONSHIP_SINGLE_NAME => [
                    'data' => [
                        'type' => self::MODEL_INCLUDE_RELATIONSHIP_PLURAL_NAME,
                        'id' => (string) $this->category->getRouteKey()
                    ]
                ]
            ]
        ];

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->withData($data)
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_SINGLE_NAME)
            ->post(route(self::MODEL_MAIN_ACTION_ROUTE));

        // Unprocessable Entity (422)
        $response->assertError(422, [
            'source' => ['pointer' => '/data/attributes/description'],
            'detail' => 'The description field is required.'
        ]);

        $response->assertSee('data\/attributes\/description');

        $this->assertDatabaseMissing(self::MODEL_PLURAL_NAME, $subcategory);
    }
}
