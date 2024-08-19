<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            DB::table('plans')->delete();

            $plansJson = File::get(database_path('seeders/json/plans.json'));
            $plans = json_decode($plansJson, true);

            foreach ($plans as $planData) {
                $translations = $planData['translations'];
                unset($planData['translations']);

                $goalId = $this->getGoalId($planData['goal']);
                unset($planData['goal']);

                $frequencyId = $this->getFrequencyId($planData['frequency']);
                unset($planData['frequency']);

                $physicalConditionId = $this->getPhysicalConditionId($planData['physicalCondition']);
                unset($planData['physicalCondition']);

                $plan = $this->createPlan($planData, $goalId, $frequencyId, $physicalConditionId);

                $this->handleTranslations($plan, $translations);
            }
        });
    }

    private function getGoalId(string $goal): int
    {
        return DB::table('goals')
            ->where('name', $goal)
            ->value('id');
    }

    private function getFrequencyId(string $frequency): int
    {
        return DB::table('frequencies')
            ->where('name', $frequency)
            ->value('id');
    }

    private function getPhysicalConditionId(string $physicalCondition): int
    {
        return DB::table('physical_conditions')
            ->where('name', $physicalCondition)
            ->value('id');
    }

    private function createPlan(array $planData, int $goalId, int $frequencyId, int $physicalConditionId): Plan
    {
        return Plan::factory()->create(array_merge(
            $planData,
            [
                'goal_id' => $goalId,
                'frequency_id' => $frequencyId,
                'physical_condition_id' => $physicalConditionId
            ]
        ));
    }

    private function handleTranslations(Plan $plan, array $translations): void
    {
        foreach ($translations as $translation) {
            $plan->translations()->create($translation);
        }
    }
}
