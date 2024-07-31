<?php

namespace Database\Seeders;

use App\Models\Muscle;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class MuscleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Muscle::query()->delete();

        Muscle::factory()->count(10)
            ->state(new Sequence(
                [
                    'name' => 'Biceps',
                    'description' => 'The biceps brachii muscle, commonly known as the biceps, is a two-headed muscle located on the front of the upper arm.',
                ],
                [
                    'name' => 'Triceps',
                    'description' => 'The triceps brachii muscle, commonly known as the triceps, is a three-headed muscle located on the back of the upper arm.',
                ],
                [
                    'name' => 'Quadriceps',
                    'description' => 'The quadriceps femoris muscle, commonly known as the quadriceps, is a group of four muscles located on the front of the thigh.',
                ],
                [
                    'name' => 'Hamstrings',
                    'description' => 'The hamstring muscles are a group of three muscles located on the back of the thigh.',
                ],
                [
                    'name' => 'Glutes',
                    'description' => 'The gluteus maximus, gluteus medius, and gluteus minimus muscles, commonly known as the glutes, are located in the buttocks.',
                ],
                [
                    'name' => 'Calves',
                    'description' => 'The calf muscles, including the gastrocnemius and soleus muscles, are located on the back of the lower leg.',
                ],
                [
                    'name' => 'Pectorals',
                    'description' => 'The pectoralis major and pectoralis minor muscles, commonly known as the pecs, are located in the chest.',
                ],
                [
                    'name' => 'Deltoids',
                    'description' => 'The deltoid muscle, commonly known as the delts, is located on the shoulder.',
                ],
                [
                    'name' => 'Latissimus Dorsi',
                    'description' => 'The latissimus dorsi muscle, commonly known as the lats, is located on the back.',
                ],
                [
                    'name' => 'Abs',
                    'description' => 'The abdominal muscles, commonly known as the abs, are located in the abdomen.',
                ],
            ));
    }
}
