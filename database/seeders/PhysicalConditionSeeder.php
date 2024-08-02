<?php

namespace Database\Seeders;

use App\Models\PhysicalCondition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PhysicalConditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            DB::table('physical_conditions')->delete();

            $physicalConditionsJson = File::get(database_path('seeders/json/physicalConditions.json'));
            $physicalConditions = json_decode($physicalConditionsJson, true);

            foreach ($physicalConditions as $physicalConditionData) {
                $translations = $physicalConditionData['translations'];
                unset($physicalConditionData['translations']);

                $physicalCondition = PhysicalCondition::factory($physicalConditionData)->create();

                foreach ($translations as $translationData) {
                    $physicalCondition->translations()->create($translationData);
                }
            }
        });
    }
}
