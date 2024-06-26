<?php

namespace Tests\Feature\Permissions;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UpdatePermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            // TODO: add seed permissions model class here
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function it_can_test()
    {
        //
    }
}
