<?php

namespace Database\Seeders;

use App\Models\Equipment;
use App\Traits\HandlesTranslations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class EquipmentSeeder extends Seeder
{
    use HandlesTranslations;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->deleteExistingEquipment();
            $equipment = $this->getEquipmentFromJson();

            foreach ($equipment as $equipmentData) {
                $this->processEquipment($equipmentData);
            }
        });
    }

    private function deleteExistingEquipment(): void
    {
        DB::table('equipments')->delete();
    }
    private function getEquipmentFromJson(): array
    {
        $equipmentJson = File::get(database_path('seeders/json/equipment.json'));
        return json_decode($equipmentJson, true);
    }
    private function processEquipment(array $equipmentData): void
    {
        $translations = $equipmentData['translations'];
        unset($equipmentData['translations']);

        $equipment = Equipment::factory($equipmentData)->create();
        $this->handleTranslations($equipment, $translations);
    }
}
