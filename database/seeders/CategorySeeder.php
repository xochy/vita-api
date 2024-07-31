<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            Category::query()->delete();

            $categories = [
                [
                    'name' => 'Chest',
                    'description' => 'Chest exercises typically target the pectoral muscles and involve movements like pressing and fly variations. They aim to build strength and muscle mass in the upper body, improving overall chest definition and power.',
                    'translations' => [
                        ['locale' => 'es', 'column' => 'name', 'translation' => 'Pecho'],
                        ['locale' => 'es', 'column' => 'description', 'translation' => 'Los ejercicios de pecho suelen apuntar a los músculos pectorales e implican movimientos como prensas y variaciones de vuelo. Su objetivo es construir fuerza y masa muscular en la parte superior del cuerpo, mejorando la definición y la potencia general del pecho.'],
                    ],
                ],
                [
                    'name' => 'Back',
                    'description' => 'Back exercises typically focus on the latissimus dorsi, trapezius, rhomboids, and erector spinae muscles to improve strength, posture, and overall upper body support. Common movements include rows, pull-ups, and deadlifts.',
                    'translations' => [
                        ['locale' => 'es', 'column' => 'name', 'translation' => 'Espalda'],
                        ['locale' => 'es', 'column' => 'description', 'translation' => 'Los ejercicios de espalda suelen centrarse en los músculos latissimus dorsi, trapecio, romboides y erector spinae para mejorar la fuerza, la postura y el soporte general del cuerpo superior. Los movimientos comunes incluyen filas, dominadas y peso muerto.'],
                    ],
                ],
                [
                    'name' => 'Shoulders',
                    'description' => 'Shoulder exercises typically involve movements that target the deltoid muscles, which include the anterior (front), lateral (side), and posterior (rear) deltoids. Common exercises include shoulder presses, lateral raises, and front raises to develop strength and size.',
                    'translations' => [
                        ['locale' => 'es', 'column' => 'name', 'translation' => 'Hombros'],
                        ['locale' => 'es', 'column' => 'description', 'translation' =>  'Los ejercicios de hombros suelen implicar movimientos que apuntan a los músculos deltoides, que incluyen los deltoides anterior (frontal), lateral (lateral) y posterior (trasero). Los ejercicios comunes incluyen prensas de hombros, elevaciones laterales y elevaciones frontales para desarrollar fuerza y tamaño.'],
                    ],
                ],
                [
                    'name' => 'Biceps',
                    'description' => 'Bicep exercises typically involve curling movements to target the bicep brachii muscle, focusing on flexing the elbow. Common examples include bicep curls with dumbbells, barbells, or resistance bands.',
                    'translations' => [
                        ['locale' => 'es', 'column' => 'name', 'translation' => 'Bíceps'],
                        ['locale' => 'es', 'column' => 'description', 'translation' => 'Los ejercicios de bíceps suelen implicar movimientos de curling para apuntar al músculo bíceps braquial, centrándose en la flexión del codo. Ejemplos comunes incluyen curl de bíceps con mancuernas, barras o bandas de resistencia.'],
                    ],
                ],
                [
                    'name' => 'Triceps',
                    'description' => 'Tricep exercises typically involve extension movements to target the tricep brachii muscle, focusing on extending the elbow. Common examples include tricep dips, tricep extensions, and tricep pushdowns.',
                    'translations' => [
                        ['locale' => 'es', 'column' => 'name', 'translation' => 'Tríceps'],
                        ['locale' => 'es', 'column' => 'description', 'translation' => 'Los ejercicios de tríceps suelen implicar movimientos de extensión para apuntar al músculo tríceps braquial, centrándose en la extensión del codo. Ejemplos comunes incluyen dips de tríceps, extensiones de tríceps y pushdowns de tríceps.'],
                    ],
                ],
                [
                    'name' => 'Forearms',
                    'description' => 'Forearm exercises typically target the muscles of the forearm, including the flexor and extensor muscles. Common exercises include wrist curls, reverse wrist curls, and hammer curls to improve grip strength and forearm size.',
                    'translations' => [
                        ['locale' => 'es', 'column' => 'name', 'translation' => 'Antebrazos'],
                        ['locale' => 'es', 'column' => 'description', 'translation' => 'Los ejercicios de antebrazos suelen apuntar a los músculos del antebrazo, incluidos los músculos flexores y extensores. Los ejercicios comunes incluyen curl de muñeca, curl de muñeca inverso y curl de martillo para mejorar la fuerza de agarre y el tamaño del antebrazo.'],
                    ],
                ],
                [
                    'name' => 'Adominals',
                    'description' => 'Abdominals exercises primarily target the rectus abdominis, which creates the “six-pack” appearance, and the obliques, which are located on the sides of the torso. They help improve core strength and stability.',
                    'translations' => [
                        ['locale' => 'es', 'column' => 'name', 'translation' => 'Abdominales'],
                        ['locale' => 'es', 'column' => 'description', 'translation' => 'Los ejercicios de abdominales se dirigen principalmente al recto abdominal, que crea la apariencia de "seis paquetes", y a los oblicuos, que se encuentran en los lados del torso. Ayudan a mejorar la fuerza y la estabilidad del núcleo.'],
                    ],
                ],
                [
                    'name' => 'Obliques',
                    'description' => 'Oblique exercises target the muscles on the sides of the abdomen, including the internal and external obliques. They enhance rotational movement and core stability, contributing to a more defined waistline and improved overall core strength.',
                    'translations' => [
                        ['locale' => 'es', 'column' => 'name', 'translation' => 'Oblicuos'],
                        ['locale' => 'es', 'column' => 'description', 'translation' => 'Los ejercicios de oblicuos se dirigen a los músculos de los lados del abdomen, incluidos los oblicuos internos y externos. Mejoran el movimiento rotacional y la estabilidad del núcleo, contribuyendo a una línea de cintura más definida y una fuerza central general mejorada.'],
                    ],
                ],
                [
                    'name' => 'Legs',
                    'description' => 'Leg exercises typically target the quadriceps, hamstrings, calves, and glutes, including movements like squats, lunges, deadlifts, and calf raises. These exercises help build strength, improve stability, and enhance overall lower body performance.',
                    'translations' => [
                        ['locale' => 'es', 'column' => 'name', 'translation' => 'Piernas'],
                        ['locale' => 'es', 'column' => 'description', 'translation' => 'Los ejercicios de piernas suelen apuntar a los cuádriceps, isquiotibiales, pantorrillas y glúteos, incluidos movimientos como sentadillas, estocadas, peso muerto y elevaciones de pantorrillas. Estos ejercicios ayudan a construir fuerza, mejorar la estabilidad y mejorar el rendimiento general de la parte inferior del cuerpo.'],
                    ],
                ],
                [
                    'name' => 'Traps',
                    'description' => 'Trap exercises typically target the upper back muscles, including the trapezius, which help in elevating, retracting, and rotating the shoulder blades. Common exercises include shrugs and upright rows, focusing on strengthening and building the muscle around the neck and upper back.',
                    'translations' => [
                        ['locale' => 'es', 'column' => 'name', 'translation' => 'Trapecios'],
                        ['locale' => 'es', 'column' => 'description', 'translation' => 'Los ejercicios de trampas suelen apuntar a los músculos de la espalda alta, incluido el trapecio, que ayudan a elevar, retraer y rotar las escápulas. Los ejercicios comunes incluyen encogimientos de hombros y filas verticales, centrándose en fortalecer y construir el músculo alrededor del cuello y la espalda alta.'],
                    ],
                ]
            ];

            foreach ($categories as $categoryData) {
                $translations = $categoryData['translations'];
                unset($categoryData['translations']); // Remove translations from the category data array

                $category = Category::factory($categoryData)->create();

                foreach ($translations as $translationData) {
                    // Assuming you have a method to handle translations
                    $category->translations()->create($translationData);
                }
            }
        });
    }
}
