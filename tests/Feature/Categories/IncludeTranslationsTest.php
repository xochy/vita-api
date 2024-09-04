<?php

namespace Tests\Feature\Categories;

use App\Models\Category;
use App\Models\User;
use Database\Seeders\PermissionsSeeders\CategoriesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IncludeTranslationsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'categories';
    const MODEL_INCLUDE_RELATIONSHIP_NAME = 'translations';
    const MODEL_SHOW_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';
    const MODEL_SHOW_RELATIONSHIP_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.translations';
    const MODEL_ES_NAME = 'Espalda baja';
    const MODEL_EN_NAME = 'Lower back';
    const MODEL_ES_DESCRIPTION = 'Descripción de la categoría en español';
    const MODEL_EN_DESCRIPTION = 'Category description in english';

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
            $this->seed(CategoriesPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function categories_can_include_translations()
    {
        $category = Category::factory(
            [
                'name' => self::MODEL_EN_NAME,
                'description' => self::MODEL_EN_DESCRIPTION,
            ]
        )->hasTranslations(
                2,
                new Sequence(
                    [
                        'locale' => 'es',
                        'column' => 'name',
                        'translation' => self::MODEL_ES_NAME,
                    ],
                    [
                        'locale' => 'es',
                        'column' => 'description',
                        'translation' => self::MODEL_ES_DESCRIPTION,
                    ]
                )
            )->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->includePaths(self::MODEL_INCLUDE_RELATIONSHIP_NAME)
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $category));

        $response->assertSee($category->translations[0]->slug);

        $response->assertJsonFragment(
            [
                'related' => route(self::MODEL_RELATED_ROUTE, $category)
            ]
        );

        $response->assertJsonFragment(
            [
                'self' => route(self::MODEL_SELF_ROUTE, $category)
            ]
        );
    }

    /** @test */
    public function categories_can_fetch_related_translations()
    {
        $category = Category::factory(
            [
                'name' => self::MODEL_EN_NAME,
                'description' => self::MODEL_EN_DESCRIPTION,
            ]
        )->hasTranslations(
                2,
                new Sequence(
                    [
                        'locale' => 'es',
                        'column' => 'name',
                        'translation' => self::MODEL_ES_NAME,
                    ],
                    [
                        'locale' => 'es',
                        'column' => 'description',
                        'translation' => self::MODEL_ES_DESCRIPTION,
                    ]
                )
            )->create();

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects('translations')
            ->get(route(self::MODEL_SHOW_RELATIONSHIP_ROUTE, $category));

        $response->assertJsonCount(2, 'data');

        $response->assertJsonPath('data.0.attributes.translation', self::MODEL_ES_NAME);
        $response->assertJsonPath('data.1.attributes.translation', self::MODEL_ES_DESCRIPTION);
    }
}
