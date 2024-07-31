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
                [
                    'name' => 'Basic',
                    'price' => 0,
                    'description' => 'The basic plan is free and includes access to all features.',
                    'translations' => [
                        ['locale' => 'es', 'column' => 'name', 'translation' => 'Básico'],
                        ['locale' => 'es', 'column' => 'description', 'translation' => 'El plan básico es gratuito e incluye acceso a todas las funciones.'],
                    ],
                ],
                [
                    'name' => 'Premium',
                    'price' => 9.99,
                    'description' => 'The premium plan includes access to all features and exclusive content.',
                    'translations' => [
                        ['locale' => 'es', 'column' => 'name', 'translation' => 'Premium'],
                        ['locale' => 'es', 'column' => 'description', 'translation' => 'El plan premium incluye acceso a todas las funciones y contenido exclusivo.'],
                    ],
                ],
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
