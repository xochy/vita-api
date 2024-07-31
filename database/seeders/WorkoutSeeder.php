<?php

namespace Database\Seeders;

use App\Models\Workout;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkoutSeeder extends Seeder
{
    const PECTORALIS_MAJOR = 'Pectoralis Major';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            DB::table('workouts')->delete();

            $workouts = [
                [
                    'name' => 'Bench press',
                    'performance' => 'Lie flat on a bench, grip the barbell slightly wider than shoulder-width, lower it to your chest, then press it back up.',
                    'comments' => 'Fundamental for building chest strength and mass.',
                    'corrections' => 'Keep your feet flat on the floor and your lower back slightly arched.',
                    'warnings' => 'Avoid bouncing the bar off your chest to prevent injury.',
                    'translations' => [
                        ['locale' => 'es', 'column' => 'name', 'translation' => 'Press de banca'],
                        ['locale' => 'es', 'column' => 'performance', 'translation' => 'Acuéstese en un banco, agarre la barra ligeramente más ancha que el ancho de los hombros, bájela al pecho y luego presiónela hacia arriba.'],
                        ['locale' => 'es', 'column' => 'comments', 'translation' => 'Fundamental para construir fuerza y masa en el pecho.'],
                        ['locale' => 'es', 'column' => 'corrections', 'translation' => 'Mantenga los pies planos en el suelo y la parte baja de la espalda ligeramente arqueada.'],
                        ['locale' => 'es', 'column' => 'warnings', 'translation' => 'Evite rebotar la barra en el pecho para prevenir lesiones.'],
                    ],
                    'subcategory' => self::PECTORALIS_MAJOR,
                ],
                [
                    'name' => 'Incline bench press',
                    'performance' => 'Set the bench to a 30-45 degree incline, perform a press similar to the flat bench press.',
                    'comments' => 'Emphasizes the upper chest.',
                    'corrections' => 'Ensure the bar path is directly above the upper chest.',
                    'warnings' => 'Maintain control to avoid shoulder strain.',
                    'translations' => [
                        ['locale' => 'es', 'column' => 'name', 'translation' => 'Press de banca inclinado'],
                        ['locale' => 'es', 'column' => 'performance', 'translation' => 'Ajuste el banco a una inclinación de 30-45 grados, realice una prensa similar a la prensa de banco plana.'],
                        ['locale' => 'es', 'column' => 'comments', 'translation' => 'Énfasis en la parte superior del pecho.'],
                        ['locale' => 'es', 'column' => 'corrections', 'translation' => 'Asegúrese de que la trayectoria de la barra esté directamente sobre la parte superior del pecho.'],
                        ['locale' => 'es', 'column' => 'warnings', 'translation' => 'Mantenga el control para evitar la tensión en los hombros.'],
                    ],
                    'subcategory' => self::PECTORALIS_MAJOR,
                ],
                [
                    'name' => 'Dumbbell flyes',
                    'performance' => 'Lie on a flat bench, hold dumbbells above your chest with palms facing each other, lower them in an arc until they are level with your chest, then return to the starting position.',
                    'comments' => 'Stretches and targets the chest muscles.',
                    'corrections' => 'Keep a slight bend in your elbows to reduce stress on the shoulder joint.',
                    'warnings' => 'Do not go too low to avoid shoulder injuries.',
                    'translations' => [
                        ['locale' => 'es', 'column' => 'name', 'translation' => 'Vuelos con mancuernas'],
                        ['locale' => 'es', 'column' => 'performance', 'translation' => 'Acuéstese en un banco plano, sostenga las mancuernas sobre el pecho con las palmas hacia arriba entre sí, bájelas en un arco hasta que estén a la altura del pecho, luego regrese a la posición inicial.'],
                        ['locale' => 'es', 'column' => 'comments', 'translation' => 'Estira y apunta a los músculos del pecho.'],
                        ['locale' => 'es', 'column' => 'corrections', 'translation' => 'Mantenga una ligera flexión en los codos para reducir el estrés en la articulación del hombro.'],
                        ['locale' => 'es', 'column' => 'warnings', 'translation' => 'No baje demasiado para evitar lesiones en el hombro.'],
                    ],
                    'subcategory' => self::PECTORALIS_MAJOR,
                ],
                [
                    'name' => 'Push-ups',
                    'performance' => 'Start in a plank position, lower your body until your chest nearly touches the floor, then push back up.',
                    'comments' => 'Versatile and can be done anywhere.',
                    'corrections' => 'Maintain a straight line from head to heels.',
                    'warnings' => 'Avoid letting your hips sag to prevent lower back strain.',
                    'translations' => [
                        ['locale' => 'es', 'column' => 'name', 'translation' => 'Flexiones'],
                        ['locale' => 'es', 'column' => 'performance', 'translation' => 'Comience en una posición de plancha, baje el cuerpo hasta que el pecho casi toque el suelo, luego empuje hacia arriba.'],
                        ['locale' => 'es', 'column' => 'comments', 'translation' => 'Versátil y se puede hacer en cualquier lugar.'],
                        ['locale' => 'es', 'column' => 'corrections', 'translation' => 'Mantenga una línea recta desde la cabeza hasta los talones.'],
                        ['locale' => 'es', 'column' => 'warnings', 'translation' => 'Evite que sus caderas se hundan para evitar la tensión en la parte baja de la espalda.'],
                    ],
                    'subcategory' => self::PECTORALIS_MAJOR,
                ],
                [
                    'name' => 'Chest dips',
                    'performance' => 'Use parallel bars, lean forward slightly, lower your body until your shoulders are below your elbows, then press back up.',
                    'comments' => 'Effective for lower chest development.',
                    'corrections' => 'Keep your elbows slightly flared to focus on the chest.',
                    'warnings' => 'Avoid excessive forward lean to protect your shoulders.',
                    'translations' => [
                        ['locale' => 'es', 'column' => 'name', 'translation' => 'Fondos de pecho'],
                        ['locale' => 'es', 'column' => 'performance', 'translation' => 'Use barras paralelas, inclínese ligeramente hacia adelante, baje el cuerpo hasta que los hombros estén por debajo de los codos, luego presione hacia arriba.'],
                        ['locale' => 'es', 'column' => 'comments', 'translation' => 'Efectivo para el desarrollo del pecho inferior.'],
                        ['locale' => 'es', 'column' => 'corrections', 'translation' => 'Mantenga los codos ligeramente abiertos para enfocarse en el pecho.'],
                        ['locale' => 'es', 'column' => 'warnings', 'translation' => 'Evite inclinarse demasiado hacia adelante para proteger sus hombros.'],
                    ],
                    'subcategory' => self::PECTORALIS_MAJOR,
                ],
            ];

            foreach ($workouts as $workoutData) {
                $translations = $workoutData['translations'];
                unset($workoutData['translations']);

                $subcategory = $workoutData['subcategory'];
                unset($workoutData['subcategory']);

                $subcategoryId = DB::table('subcategories')
                    ->where('name', $subcategory)
                    ->value('id');

                $workout = Workout::factory()->create(array_merge(
                    $workoutData,
                    [
                        'subcategory_id' => $subcategoryId
                    ]
                ));

                foreach ($translations as $translation) {
                    $workout->translations()->create($translation);
                }
            }
        });
    }
}
