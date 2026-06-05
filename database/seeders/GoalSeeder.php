<?php

namespace Database\Seeders;

use App\Models\Goal;
use App\Traits\HandlesTranslations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class GoalSeeder extends Seeder
{
    use HandlesTranslations;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->deleteExistingGoals();
            $goals = $this->getGoalsFromJson();

            foreach ($goals as $goalData) {
                $this->processGoal($goalData);
            }
        });
    }

    private function deleteExistingGoals(): void
    {
        DB::table('goals')->delete();
    }

    private function getGoalsFromJson(): array
    {
        $goalsJson = File::get(database_path('seeders/json/goals.json'));
        return json_decode($goalsJson, true);
    }

    private function processGoal(array $goalData): void
    {
        $translations = $goalData['translations'];
        unset($goalData['translations']);

        $goal = Goal::factory($goalData)->create();
        $this->handleTranslations($goal, $translations);
    }
}
