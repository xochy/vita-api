<?php

namespace Database\Seeders;

use App\Models\PhysicalCondition;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class PhysicalConditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PhysicalCondition::query()->delete();

        PhysicalCondition::factory()->count(10)
            ->state(new Sequence(
                [
                    'name' => 'Initial or Beginner',
                    'description' => 'Someone who is starting a training program and may have low initial fitness, endurance, and strength.',
                ],
                [
                    'name' => 'Intermediate',
                    'description' => 'Indicates a level of fitness that has improved from the initial state but has not yet reached an advanced level. Can perform certain exercises and activities with greater ease.',
                ],
                [
                    'name' => 'Advanced',
                    'description' => 'Refers to a person with a significant level of fitness. Can handle intense workouts, has advanced strength, endurance, and flexibility.',
                ],
                [
                    'name' => 'Maintenance',
                    'description' => 'People who have already achieved their fitness goals and are following a training plan to maintain their current physical state.',
                ],
                [
                    'name' => 'Recovery',
                    'description' => 'Someone who is in the process of recovering from an injury or illness and needs a tailored training program to gradually improve their physical condition.',
                ],
                [
                    'name' => 'Specialized',
                    'description' => 'A fitness state focused on specific goals, such as preparing for a sports competition, gaining muscle mass, losing weight, etc.',
                ],
                [
                    'name' => 'Rehabilitation',
                    'description' => 'For those who have gone through an injury, surgery, or other physical limitation and need a specialized training program for recovery.',
                ],
                [
                    'name' => 'Cross-Training',
                    'description' => 'Incorporating different forms of training to improve overall fitness and avoid monotony.',
                ],
                [
                    'name' => 'Pre-Competition',
                    'description' => 'A specific fitness state for those who are preparing for a competition, with a focus on optimizing performance.',
                ],
                [
                    'name' => 'Health Maintenance',
                    'description' => 'For individuals seeking to maintain a basic level of fitness for overall health, without specific performance goals.',
                ],
            ))
            ->create();
    }
}
