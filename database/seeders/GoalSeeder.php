<?php

namespace Database\Seeders;

use App\Models\Goal;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class GoalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Goal::query()->delete();

        //         1. **Objetivos de Fuerza:**
        //    - Aumentar el peso en los levantamientos.
        //    - Incrementar el número de repeticiones en ciertos ejercicios.
        //    - Desarrollar fuerza específica en grupos musculares clave.

        //         2. **Objetivos de Resistencia Cardiovascular:**
        //    - Mejorar la capacidad aeróbica a través de ejercicios cardiovasculares.
        //    - Aumentar la duración o la intensidad de las sesiones de cardio.
        //    - Lograr ciertas distancias o tiempos en actividades cardiovasculares específicas.

        //         3. **Objetivos de Composición Corporal:**
        //    - Perder peso.
        //    - Reducir el porcentaje de grasa corporal.
        //    - Ganar masa muscular.

        // 4. **Objetivos de Flexibilidad:**
        //    - Mejorar la flexibilidad en ciertas articulaciones o grupos musculares.
        //    - Ser capaz de realizar ciertos movimientos o posturas específicas.

        //         5. **Objetivos Específicos del Grupo Muscular:**
        //    - Desarrollar músculos específicos.
        //    - Mejorar la definición muscular en áreas específicas del cuerpo.

        // 6. **Objetivos de Rendimiento Funcional:**
        //    - Mejorar la capacidad para realizar actividades diarias.
        //    - Desarrollar habilidades específicas para deportes o actividades recreativas.

        // 7. **Objetivos de Equilibrio y Estabilidad:**
        //    - Mejorar el equilibrio a través de ejercicios específicos.
        //    - Fortalecer los músculos estabilizadores.

        //         8. **Objetivos Generales de Salud:**
        //    - Mejorar los marcadores de salud, como la presión arterial, el colesterol, etc.
        //    - Reducir el riesgo de enfermedades relacionadas con el estilo de vida.

        // 9. **Objetivos Psicológicos:**
        //    - Reducir el estrés a través del ejercicio.
        //    - Mejorar el bienestar emocional y mental.
        //    - Establecer hábitos saludables y sostenibles.

        //     10. **Objetivos de Flexibilidad de Tiempo:**
        // - Ajustar el tiempo de entrenamiento de acuerdo con las restricciones de tiempo personales.

        Goal::factory()->count(10)
            ->state(new Sequence(
                [
                    'name' => 'Increase weight in lifts',
                    'description' => 'Strength goal to increase the amount of weight lifted in specific exercises such as squats, bench press, deadlifts, etc.',
                ],
                [
                    'name' => 'Increase the number of repetitions in certain exercises',
                    'description' => 'Strength goal to increase the number of repetitions performed in specific exercises, which can improve endurance and muscle strength.',
                ],
                [
                    'name' => 'Develop specific strength in key muscle groups',
                    'description' => 'Strength goal to improve strength in specific muscle groups such as upper body, lower body, back, arms, etc.',
                ],
                [
                    'name' => 'Improve aerobic capacity through cardiovascular exercises',
                    'description' => 'Cardiovascular endurance goal to improve aerobic capacity and heart health through cardiovascular activities such as running, swimming, cycling, etc.',
                ],
                [
                    'name' => 'Increase the duration or intensity of cardio sessions',
                    'description' => 'Cardiovascular endurance goal to increase the duration or intensity of cardio sessions, which can improve endurance and cardiovascular health.',
                ],
                [
                    'name' => 'Achieve certain distances or times in specific cardiovascular activities',
                    'description' => 'Cardiovascular endurance goal to reach certain distances or times in specific cardiovascular activities, setting clear goals to improve endurance and performance.',
                ],
                [
                    'name' => 'Lose weight',
                    'description' => 'Body composition goal to reduce total body weight, usually through a combination of diet and exercise.',
                ],
                [
                    'name' => 'Reduce body fat percentage',
                    'description' => 'Body composition goal to decrease body fat percentage, which may involve fat loss and maintenance or increase of lean muscle mass.',
                ],
                [
                    'name' => 'Gain muscle mass',
                    'description' => 'Body composition goal to increase lean muscle mass, usually through a strength training program and proper diet.',
                ],
                [
                    'name' => 'Improve flexibility in certain joints or muscle groups',
                    'description' => 'Flexibility goal to increase range of motion in certain joints or muscle groups, which can improve posture, prevent injuries, and facilitate certain movements.',
                ],
                [
                    'name' => 'Be able to perform certain specific movements or postures',
                    'description' => 'Flexibility goal to be able to perform certain specific movements or postures, such as splits, backbends, etc., which can be beneficial for certain activities or disciplines.',
                ],
                [
                    'name' => 'Develop specific muscles',
                    'description' => 'Specific muscle group goal to develop specific muscles such as biceps, triceps, quadriceps, hamstrings, etc., through isolation and compound exercises.',
                ],
                [
                    'name' => 'Improve muscle definition in specific areas of the body',
                    'description' => 'Specific muscle group goal to improve muscle definition in specific areas of the body such as abs, shoulders, glutes, etc., through reducing body fat percentage and increasing muscle mass.',
                ],
                [
                    'name' => 'Improve the ability to perform daily activities',
                    'description' => 'Functional performance goal to improve the ability to perform daily activities such as climbing stairs, lifting heavy objects, walking long distances, etc., which can improve quality of life and functional independence.',
                ],
                [
                    'name' => 'Develop specific skills for sports or recreational activities',
                    'description' => 'Functional performance goal to develop specific skills for sports or recreational activities such as improving speed, agility, power, endurance, etc., to enhance performance and enjoyment in the chosen activity.',
                ],
                [
                    'name' => 'Improve balance through specific exercises',
                    'description' => 'Balance and stability goal to improve balance through specific exercises such as single-leg balance exercises, core stability exercises, etc., which can prevent injuries and enhance performance in certain activities.',
                ],
                [
                    'name' => 'Strengthen stabilizer muscles',
                    'description' => 'Balance and stability goal to strengthen stabilizer muscles, which are responsible for maintaining proper posture and alignment during movement, which can improve stability and prevent injuries.',
                ],
                [
                    'name' => 'Improve health markers such as blood pressure, cholesterol, etc.',
                    'description' => 'General health goal to improve health markers such as blood pressure, cholesterol, blood glucose, etc., through diet, exercise, and other lifestyle changes.',
                ],
                [
                    'name' => 'Reduce the risk of lifestyle-related diseases',
                    'description' => 'General health goal to reduce the risk of lifestyle-related diseases such as diabetes, obesity, heart diseases, etc., through prevention and management of risk factors.',
                ],
                [
                    'name' => 'Reduce stress through exercise',
                    'description' => 'Psychological goal to reduce stress and improve emotional and mental well-being through exercise, which can release endorphins, reduce anxiety, and improve mood.',
                ],
                [
                    'name' => 'Improve emotional and mental well-being',
                    'description' => 'Psychological goal to improve emotional and mental well-being through exercise, meditation, therapy, etc., which can improve mental health and overall quality of life.',
                ],
                [
                    'name' => 'Establish healthy and sustainable habits',
                    'description' => 'Psychological goal to establish healthy and sustainable habits in diet, exercise, sleep, etc., which can improve long-term health and prevent chronic diseases.',
                ],
                [
                    'name' => 'Adjust training time according to personal time constraints',
                    'description' => 'Time flexibility goal to adjust training time according to personal time constraints, which may involve shorter training sessions, home workouts, etc.',
                ],
            ))
            ->create();
    }
}
