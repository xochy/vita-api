<?php

namespace Tests\Feature\Permissions;

use App\Models\User;
use Database\Seeders\permissionsSeeders\PermissionsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaginatePermissionsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'permissions';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.index';

    const MODEL_SIZE_PARAM_NAME = 'page[size]';
    const MODEL_NUMBER_PARAM_NAME = 'page[number]';

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(PermissionsPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('superAdmin');
    }

    /** @test */
    public function can_fetch_paginated_permissions()
    {
        // Create 7 permissions randomly
        $permissions = [
            [
                'name'         => 'read articles',
                'display_name' => 'Leer artículos',
                'action'       => 'read',
                'subject'      => 'article'
            ],
            [
                'name'         => 'create articles',
                'display_name' => 'Crear artículos',
                'action'       => 'create',
                'subject'      => 'article'
            ],
            [
                'name'         => 'update articles',
                'display_name' => 'Actualizar artículos',
                'action'       => 'update',
                'subject'      => 'article'
            ],
            [
                'name'         => 'delete articles',
                'display_name' => 'Eliminar artículos',
                'action'       => 'delete',
                'subject'      => 'article'
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                'name'         => $permission['name'],
                'display_name' => $permission['display_name'],
                'action'       => $permission['action'],
                'subject'      => $permission['subject']
            ]);
        }

        $url = route(
            self::MODEL_MAIN_ACTION_ROUTE,
            [
                self::MODEL_SIZE_PARAM_NAME => 2, self::MODEL_NUMBER_PARAM_NAME => 3
            ]
        );

        $response = $this->actingAs($this->user)->jsonApi()
            ->expects(self::MODEL_PLURAL_NAME)->get($url);

        $response->assertJsonStructure(
            [
                'links' => ['first', 'prev', 'next', 'last']
            ]
        );

        $response->assertJsonFragment(
            [
                'first' => route(
                    self::MODEL_MAIN_ACTION_ROUTE,
                    [
                        self::MODEL_NUMBER_PARAM_NAME => 1, self::MODEL_SIZE_PARAM_NAME => 2
                    ]
                ),
                'prev'  => route(
                    self::MODEL_MAIN_ACTION_ROUTE,
                    [
                        self::MODEL_NUMBER_PARAM_NAME => 2, self::MODEL_SIZE_PARAM_NAME => 2
                    ]
                ),
                'next'  => route(
                    self::MODEL_MAIN_ACTION_ROUTE,
                    [
                        self::MODEL_NUMBER_PARAM_NAME => 4, self::MODEL_SIZE_PARAM_NAME => 2
                    ]
                ),
                'last'  => route(
                    self::MODEL_MAIN_ACTION_ROUTE,
                    [
                        self::MODEL_NUMBER_PARAM_NAME => 5, self::MODEL_SIZE_PARAM_NAME => 2
                    ]
                ),
            ]
        );
    }
}
