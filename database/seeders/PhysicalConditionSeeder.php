<?php

namespace Database\Seeders;

use App\Models\PhysicalCondition;
use App\Traits\HandlesTranslations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PhysicalConditionSeeder extends Seeder
{
    use HandlesTranslations;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->deleteExistingPhysicalConditions();
            $physicalConditions = $this->getPhysicalConditionsFromJson();

            foreach ($physicalConditions as $physicalConditionData) {
                $this->processPhysicalCondition($physicalConditionData);
            }
        });
    }

    private function deleteExistingPhysicalConditions(): void
    {
        DB::table('physical_conditions')->delete();
    }

    private function getPhysicalConditionsFromJson(): array
    {
        $physicalConditionsJson = File::get(database_path('seeders/json/physicalConditions.json'));
        return json_decode($physicalConditionsJson, true);
    }

    private function processPhysicalCondition(array $physicalConditionData): void
    {
        $translations = $physicalConditionData['translations'];
        unset($physicalConditionData['translations']);

        $physicalCondition = PhysicalCondition::factory($physicalConditionData)->create();
        $this->handleTranslations($physicalCondition, $translations);
    }
}
