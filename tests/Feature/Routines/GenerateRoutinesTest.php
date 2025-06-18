<?php

namespace Tests\Feature\Routines;

use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\EquipmentSeeder;
use Database\Seeders\GoalSeeder;
use Database\Seeders\MuscleSeeder;
use Database\Seeders\permissionsSeeders\RoutinesPermissionsSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\VariationSeeder;
use Database\Seeders\WorkoutSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GenerateRoutinesTest extends TestCase
{
    use RefreshDatabase;

    const MODEL_PLURAL_NAME = 'routines';
    const MODEL_PROPOSE_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.propose';
    const MODEL_GENERATE_ACTION_ROUTE = 'v1.' . self::MODEL_PLURAL_NAME . '.generate';

    protected User $user;
    protected string $token;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            $this->seed(RoutinesPermissionsSeeder::class);

            $this->seed(MuscleSeeder::class);
            $this->seed(CategorySeeder::class);
            $this->seed(EquipmentSeeder::class);

            $this->seed(WorkoutSeeder::class);
            $this->seed(VariationSeeder::class);
        }

        [$this->user, $this->token] = $this->createUserWithToken();
    }

    /** @test */
    public function can_propose_routines()
    {
        $data = $this->getRoutineProposalData();

        $response = $this->jsonApi()
            ->withHeaders(['Authorization' => $this->token])
            ->withData($data)
            ->post(route(self::MODEL_PROPOSE_ACTION_ROUTE));

        $workoutIds = $response->json('data.*.id');

        $this->assertNotEmpty($workoutIds);
    }

    /** @test */
    public function gender_is_required_for_proposing_routines()
    {
        $data = $this->getRoutineProposalData();
        unset($data['gender']);

        $response = $this->jsonApi()
            ->withHeaders(['Authorization' => $this->token])
            ->withData($data)
            ->post(route(self::MODEL_PROPOSE_ACTION_ROUTE));

        dd($response->json());
    }

    /** @test */
    public function can_generate_routines()
    {
        $data = $this->getRoutineProposalData();

        $response = $this->jsonApi()
            ->withHeaders(['Authorization' => $this->token])
            ->withData($data)
            ->post(route(self::MODEL_PROPOSE_ACTION_ROUTE));

        $workoutIds = $response->json('data.*.id');

        $data = array_merge($data, [
            'name' => 'Generated Routine',
            'workout_ids' => $workoutIds,
        ]);

        $response = $this->jsonApi()
            ->withHeaders(['Authorization' => $this->token])
            ->withData($data)
            ->post(route(self::MODEL_GENERATE_ACTION_ROUTE));

        $routine = $response->json('data');
        $this->assertNotEmpty($routine);

        $this->assertDatabaseHas('routines', [
            'name' => 'Generated Routine',
        ]);

        foreach ($workoutIds as $workoutId) {
            $this->assertDatabaseHas('routine_workout', [
                'routine_id' => $routine['id'],
                'workout_id' => $workoutId,
            ]);
        }
    }



    protected function getRoutineProposalData(array $overrides = []): array
    {
        $default = [
            'user_id'       => $this->user->id,
            'gender'        => 'male',
            'age'           => 25,
            'goal'          => 'lose weight',
            'level'         => 'beginner',
            'equipment_ids' => [1, 2],
            'muscle_ids'    => [1, 2, 3],
        ];

        return array_merge($default, $overrides);
    }
}
