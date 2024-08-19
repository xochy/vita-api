<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->deleteExistingUsers();
            $users = $this->getUsersFromJson();

            foreach ($users as $userData) {
                $this->processUser($userData);
            }
        });
    }

    private function deleteExistingUsers(): void
    {
        DB::table('users')->delete();
    }

    private function getUsersFromJson(): array
    {
        $usersJson = File::get(database_path('seeders/json/users.json'));
        return json_decode($usersJson, true);
    }

    private function processUser(array $userData): void
    {
        $password = $userData['password'];
        unset($userData['password']);

        $role = $userData['role'];
        unset($userData['role']);

        $plans = $userData['plans'] ?? [];
        unset($userData['plans']);

        $user = User::factory()->create(array_merge(
            $userData,
            [
                'email_verified_at' => now(),
                'password' => bcrypt($password),
            ]
        ));

        $user->assignRole($role);
        $this->attachPlansToUser($user, $plans);
    }

    private function attachPlansToUser(User $user, array $plans): void
    {
        foreach ($plans as $plan) {
            $planId = DB::table('plans')
                ->where('name', $plan['name'])
                ->value('id');

            $user->plans()->attach($planId);
        }
    }
}
