<?php

namespace Database\Seeders;

use App\Models\Variation;
use App\Traits\HandlesTranslations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class VariationSeeder extends Seeder
{
    use HandlesTranslations;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->deleteExistingVariations();
            $variations = $this->getVariationsFromJson();

            foreach ($variations as $variationData) {
                $this->processVariation($variationData);
            }
        });
    }

    private function deleteExistingVariations(): void
    {
        DB::table('variations')->delete();
    }

    private function getVariationsFromJson(): array
    {
        $variationsJson = File::get(database_path('seeders/json/variations.json'));
        return json_decode($variationsJson, true);
    }

    private function processVariation(array $variationData): void
    {
        $muscles = $variationData['muscles'];
        unset($variationData['muscles']);

        $translations = $variationData['translations'];
        unset($variationData['translations']);

        $workout = $variationData['workout'];
        unset($variationData['workout']);

        $workoutId = $this->getWorkoutIdByName($workout);

        $variation = Variation::factory()->create(array_merge(
            $variationData,
            ['workout_id' => $workoutId]
        ));

        $this->attachMusclesToVariation($variation, $muscles);
        $this->handleTranslations($variation, $translations);
    }

    private function getWorkoutIdByName(string $workoutName): int
    {
        return DB::table('workouts')
            ->where('name', $workoutName)
            ->value('id');
    }

    private function attachMusclesToVariation(Variation $variation, array $muscles): void
    {
        foreach ($muscles as $muscle) {
            $muscleId = DB::table('muscles')
                ->where('name', $muscle['name'])
                ->value('id');

            $variation->muscles()->attach($muscleId);
        }
    }
}
