<?php

namespace Tests\Feature\Auth;

use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RefreshTokenTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'categories';
    const MODEL_SHOW_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.show';

    protected User $user;
    protected Carbon $pastDay;
    protected Carbon $tomorrowDay;
    protected array $permissions;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');

        $this->pastDay = now()->subDays(1);
        $this->tomorrowDay = now()->addDays(1);
        $this->permissions = $this->user->getAllPermissions()->pluck('name')->toArray();
    }

    /** @test */
    public function can_make_request_with_valid_token_expired_date()
    {
        $token = $this->user->createToken(
            'test',
            $this->permissions,
            $this->tomorrowDay
        )
            ->plainTextToken;

        $category = Category::factory()->create();

        $response = $this->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $category));

        $response->assertFetchedOne(
            [
                'type' => self::MODEL_PLURAL_NAME,
                'id' => (string) $category->getRouteKey(),
                'attributes' => [
                    'name'        => $category->name,
                    'description' => $category->description,
                    'slug'        => $category->slug,
                ],
                'links' => [
                    'self' => route(self::MODEL_SHOW_ACTION_ROUTE, $category)
                ]
            ]
        );
    }

    /** @test */
    public function cannot_make_request_with_invalid_token_expired_date()
    {
        $token = $this->user->createToken(
            'test',
            $this->permissions,
            $this->pastDay
        )
            ->plainTextToken;

        $category = Category::factory()->create();

        $response = $this->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->get(route(self::MODEL_SHOW_ACTION_ROUTE, $category));

        $response->assertError(
            400,
            [
                'detail' => __('auth.token_expired')
            ]
        );
    }
}
