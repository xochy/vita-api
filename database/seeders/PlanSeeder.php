<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            DB::table('plans')->delete();

            $plans = [
                
            ];

            foreach ($plans as $planData) {
                $translations = $planData['translations'];
                unset($planData['translations']);

                $plan = Plan::create($planData);

                foreach ($translations as $translation) {
                    $plan->translations()->create($translation);
                }
            }
        });
    }
}
