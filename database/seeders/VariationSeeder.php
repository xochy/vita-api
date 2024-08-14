<?php

namespace Database\Seeders;

use App\Models\Variation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class VariationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            DB::table('variations')->delete();

            $variationsJson = File::get(database_path('seeders/json/variations.json'));
            $variations = json_decode($variationsJson, true);

            foreach ($variations as $variationData) {
                $muscles = $variationData['muscles'];
                unset($variationData['muscles']);

                $translations = $variationData['translations'];
                unset($variationData['translations']);

                $workout = $variationData['workout'];
                unset($variationData['workout']);

                $workoutId = DB::table('workouts')
                    ->where('name', $workout)
                    ->value('id');

                $variation = Variation::factory()->create(array_merge(
                    $variationData,
                    [
                        'workout_id' => $workoutId
                    ]
                ));

                foreach ($muscles as $muscle) {
                    $muscleId = DB::table('muscles')
                        ->where('name', $muscle['name'])
                        ->value('id');

                    $variation->muscles()->attach($muscleId);
                }

                foreach ($translations as $translation) {
                    $variation->translations()->create($translation);
                }
            }
        });
    }
}
