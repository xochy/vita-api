<?php

namespace Database\Seeders;

use App\Models\Frequency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class FrequencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            DB::table('goals')->delete();

            $frequenciesJson = File::get(database_path('seeders/json/frequencies.json'));
            $frequencies = json_decode($frequenciesJson, true);

            foreach ($frequencies as $frequencyData) {
                $translations = $frequencyData['translations'];
                unset($frequencyData['translations']);

                $frequency = Frequency::factory($frequencyData)->create();

                foreach ($translations as $translationData) {
                    $frequency->translations()->create($translationData);
                }
            }
        });
    }
}
