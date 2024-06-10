<?php

namespace Database\Seeders;

use App\Models\Frequency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class FrequencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Frequency::query()->delete();

        Frequency::factory()->count(10)
            ->state(new Sequence(
                [
                    'name' => '3 times per week',
                    'description' => 'Frequency of sessions per week suitable for beginners or those with busy schedules.'
                ],
                [
                    'name' => '4-5 times per week',
                    'description' => 'Common frequency of sessions per week for most general training programs.'
                ],
                [
                    'name' => '6-7 times per week',
                    'description' => 'Frequency of sessions per week for those with specific goals such as weight loss or sports performance.'
                ],
                [
                    'name' => 'Full body training',
                    'description' => 'Frequency per muscle group that covers all muscle groups in each session (2-3 times per week).'
                ],
                [
                    'name' => 'Muscle group split',
                    'description' => 'Frequency per muscle group that focuses on a specific group per session (legs, back, chest, etc.), allowing more focus on each group (4-6 times per week).'
                ],
                [
                    'name' => 'Daily cardio sessions',
                    'description' => 'Frequency to improve cardiovascular health.'
                ],
                [
                    'name' => 'Intermittent cardio',
                    'description' => 'Cardio frequency that alternates cardio sessions with strength training (3-5 times per week).'
                ],
                [
                    'name' => 'Full rest days',
                    'description' => 'Frequency of important rest for recovery, especially after intense sessions.'
                ],
                [
                    'name' => 'Active training on rest days',
                    'description' => 'Frequency of rest that includes lighter activities such as walking or yoga.'
                ],
                [
                    'name' => 'Consistent daily training',
                    'description' => 'Frequency of routine change that consists of the same routine every day.'
                ],
                [
                    'name' => 'Weekly or monthly variability',
                    'description' => 'Frequency of routine change that involves regular changes in exercises, repetitions, sets, or training type.'
                ],
                [
                    'name' => 'High-intensity interval training (HIIT)',
                    'description' => 'Frequency of intensity that involves shorter but intense sessions (2-3 times per week).'
                ],
                [
                    'name' => 'Moderate training',
                    'description' => 'Frequency of intensity that involves longer sessions at moderate intensity (3-5 times per week).'
                ],
                [
                    'name' => 'Linear training',
                    'description' => 'Frequency of periodization that involves a gradual increase in intensity or load over time.'
                ],
                [
                    'name' => 'Undulating or cyclic training',
                    'description' => 'Frequency of periodization that involves variations in intensity and volume in different phases of the program.'
                ],
                [
                    'name' => 'Regular tracking',
                    'description' => 'Frequency of evaluation that involves assessing progress every 4-6 weeks.'
                ],
                [
                    'name' => 'Less frequent evaluations',
                    'description' => 'Frequency of evaluation ideal for long-term programs with longer-term goals.'
                ],
            ))
            ->create();
    }
}
