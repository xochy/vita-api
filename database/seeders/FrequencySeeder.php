<?php

namespace Database\Seeders;

use App\Models\Frequency;
use App\Traits\HandlesTranslations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class FrequencySeeder extends Seeder
{
    use HandlesTranslations;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->deleteExistingFrequencies();
            $frequencies = $this->getFrequenciesFromJson();

            foreach ($frequencies as $frequencyData) {
                $this->processFrequency($frequencyData);
            }
        });
    }

    private function deleteExistingFrequencies(): void
    {
        DB::table('frequencies')->delete();
    }

    private function getFrequenciesFromJson(): array
    {
        $frequenciesJson = File::get(database_path('seeders/json/frequencies.json'));
        return json_decode($frequenciesJson, true);
    }

    private function processFrequency(array $frequencyData): void
    {
        $translations = $frequencyData['translations'];
        unset($frequencyData['translations']);

        $frequency = Frequency::factory($frequencyData)->create();
        $this->handleTranslations($frequency, $translations);
    }
}
