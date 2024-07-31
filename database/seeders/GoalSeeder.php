<?php

namespace Database\Seeders;

use App\Models\Goal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class GoalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            DB::table('goals')->delete();

            $goalsJson = File::get(database_path('seeders/json/goals.json'));
            $goals = json_decode($goalsJson, true);

            foreach ($goals as $goalData) {
                $goal = Goal::create([
                    'name' => $goalData['name'],
                    'description' => $goalData['description'],
                ]);

                foreach ($goalData['translations'] as $translation) {
                    $goal->translations()->create([
                        'locale' => $translation['locale'],
                        'column' => $translation['column'],
                        'translation' => $translation['translation'],
                    ]);
                }
            }
        });
    }
}
