<?php

namespace Tests\Feature\Medias;

use App\Models\User;
use Database\Seeders\PermissionsSeeders\DirectoriesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DownloadMediasTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('superAdmin');
        $this->seed(DirectoriesPermissionsSeeder::class);
    }
}
