<?php

namespace Tests\Feature\Equipments;

use App\Models\User;
use Database\Seeders\PermissionsSeeders\EquipmentsPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SortEquipmentsTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'equipments';
    const MODEL_MAIN_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.index';

    const MODEL_ALFA_NAME = 'alfa name';
    const MODEL_BETA_NAME = 'beta name';
    const MODEL_GAMA_NAME = 'gama name';

    const MODEL_SORT_PARAM_VALUE = 'name';

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(EquipmentsPermissionsSeeder::class);
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    /** @test */
    public function it_can_test()
    {
        //
    }
}
