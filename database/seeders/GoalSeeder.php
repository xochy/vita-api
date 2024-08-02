<?php

namespace Database\Seeders;

use App\Models\Goal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class GoalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            DB::table('goals')->delete();

            $goalsJson = File::get(database_path('seeders/json/goals.json'));
            $goals = json_decode($goalsJson, true);

            foreach ($goals as $goalData) {
                $translations = $goalData['translations'];
                unset($goalData['translations']);

                $goal = Goal::factory($goalData)->create();

                foreach ($translations as $translationData) {
                    $goal->translations()->create($translationData);
                }
            }
        });
    }
}
