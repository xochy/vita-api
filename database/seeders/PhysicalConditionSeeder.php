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
                    'name' => 'Inicial o Principiante',
                    'description' => 'Alguien que está comenzando un programa de entrenamiento y puede tener baja condición física, resistencia y fuerza inicialmente.',
                ],
                [
                    'name' => 'Intermedio',
                    'description' => 'Indica un nivel de condición física que ha mejorado desde el estado inicial pero que aún no alcanza un nivel avanzado. Puede realizar ciertos ejercicios y actividades con mayor facilidad.',
                ],
                [
                    'name' => 'Avanzado',
                    'description' => 'Se refiere a una persona con un nivel significativo de condición física. Puede manejar entrenamientos intensos, tiene fuerza, resistencia y flexibilidad avanzadas.',
                ],
                [
                    'name' => 'Mantenimiento',
                    'description' => 'Personas que ya han alcanzado sus objetivos de condición física y están siguiendo un plan de entrenamiento para mantener su estado físico actual.',
                ],
                [
                    'name' => 'Recuperación',
                    'description' => 'Alguien que está en proceso de recuperación de una lesión o enfermedad y necesita un programa de entrenamiento adaptado para mejorar gradualmente su estado físico.',
                ],
                [
                    'name' => 'Especializado',
                    'description' => 'Un estado físico enfocado en objetivos específicos, como la preparación para una competición deportiva, la ganancia de masa muscular, la pérdida de peso, etc.',
                ],
                [
                    'name' => 'Rehabilitación',
                    'description' => 'Para aquellos que han pasado por una lesión, cirugía u otra limitación física y necesitan un programa de entrenamiento especializado para la recuperación.',
                ],
                [
                    'name' => 'Cross-Training',
                    'description' => 'La incorporación de diferentes formas de entrenamiento para mejorar la condición física general y evitar la monotonía.',
                ],
                [
                    'name' => 'Pre-Competición',
                    'description' => 'Un estado físico específico para aquellos que se están preparando para una competición, con un enfoque en la optimización del rendimiento.',
                ],
                [
                    'name' => 'Mantenimiento de la Salud',
                    'description' => 'Para personas que buscan mantener un nivel básico de condición física para la salud general, sin objetivos específicos de rendimiento.',
                ],
            ))
            ->create();
    }
}
