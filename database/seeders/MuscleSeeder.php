<?php

namespace Database\Seeders;

use App\Models\Muscle;
use App\Traits\HandlesTranslations;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class MuscleSeeder extends Seeder
{
    use HandlesTranslations;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            DB::table('muscles')->delete();

            $musclesJson = File::get(database_path('seeders/json/muscles.json'));
            $muscles = json_decode($musclesJson, true);

            foreach ($muscles as $muscleData) {
                $translations = $muscleData['translations'];
                unset($muscleData['translations']);

                $muscle = $this->createMuscle($muscleData);

                $this->handleTranslations($muscle, $translations);
            }
        });
    }

    private function createMuscle(array $muscleData): Muscle
    {
        return Muscle::factory($muscleData)->create();
    }
}
