<?php

namespace Database\Seeders;

use App\Models\Subcategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubcategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            Subcategory::query()->delete();

            $subcategories = [
                [
                    'name' => 'Pectoralis Major',
                    'description' => 'The large, upper chest muscle responsible for pushing movements and arm adduction.',
                    'translations' => [
                        ['locale' => 'es', 'column' => 'name', 'translation' => 'Pectoral Mayor'],
                        ['locale' => 'es', 'column' => 'description', 'translation' => 'El músculo grande y superior del pecho responsable de los movimientos de empuje y aducción del brazo.'],
                    ],
                    'category' => 'Chest',
                ],
                [
                    'name' => 'Pectoralis Minor',
                    'description' => 'Located beneath the pectoralis major, it helps stabilize the shoulder blade and assist in shoulder movements.',
                    'translations' => [
                        ['locale' => 'es', 'column' => 'name', 'translation' => 'Pectoral Menor'],
                        ['locale' => 'es', 'column' => 'description', 'translation' => 'Ubicado debajo del pectoral mayor, ayuda a estabilizar la escápula y a asistir en los movimientos del hombro.'],
                    ],
                    'category' => 'Chest',
                ],
                [
                    'name' => 'Serratus Anterior',
                    'description' => 'Found on the side of the chest, it aids in the movement and stabilization of the shoulder blade.',
                    'translations' => [
                        ['locale' => 'es', 'column' => 'name', 'translation' => 'Serrato Anterior'],
                        ['locale' => 'es', 'column' => 'description', 'translation' => 'Encontrado en el costado del pecho, ayuda en el movimiento y estabilización de la escápula.'],
                    ],
                    'category' => 'Chest',
                ],
                [
                    'name' => 'Latissimus Dorsi',
                    'description' => 'The largest muscles in the back, responsible for movements like pulling the arms down and back, commonly worked with pull-ups and lat pulldowns.',
                    'translations' => [
                        ['locale' => 'es', 'column' => 'name', 'translation' => 'Dorsal Ancho'],
                        ['locale' => 'es', 'column' => 'description', 'translation' => 'Los músculos más grandes de la espalda, responsables de movimientos como tirar de los brazos hacia abajo y hacia atrás, comúnmente trabajados con dominadas y poleas altas.'],
                    ],
                    'category' => 'Back',
                ],
                [
                    'name' => 'Trapezius',
                    'description' => 'Upper back muscles that help with moving, rotating, and stabilizing the shoulder blades; often targeted with shrugs and rows.',
                    'translations' => [
                        ['locale' => 'es', 'column' => 'name', 'translation' => 'Trapecio'],
                        ['locale' => 'es', 'column' => 'description', 'translation' => 'Músculos de la parte superior de la espalda que ayudan a mover, rotar y estabilizar las escápulas; a menudo se trabajan con encogimientos y filas.'],
                    ],
                    'category' => 'Back',
                ],
                [
                    'name' => 'Rhomboids',
                    'description' => 'Located between the shoulder blades, these muscles retract the scapula and are engaged in rowing movements.',
                    'translations' => [
                        ['locale' => 'es', 'column' => 'name', 'translation' => 'Romboides'],
                        ['locale' => 'es', 'column' => 'description', 'translation' => 'Ubicados entre las escápulas, estos músculos retraen la escápula y participan en movimientos de remo.'],
                    ],
                    'category' => 'Back',
                ],
                [
                    'name' => 'Erector Spinae',
                    'description' => 'A group of muscles running along the spine that extend and rotate the back, crucial for deadlifts and back extensions.',
                    'translations' => [
                        ['locale' => 'es', 'column' => 'name', 'translation' => 'Erectores de la Espina'],
                        ['locale' => 'es', 'column' => 'description', 'translation' => 'Un grupo de músculos que recorren la columna vertebral y extienden y rotan la espalda, crucial para los pesos muertos y las extensiones de espalda.'],
                    ],
                    'category' => 'Back',
                ],
                [
                    'name' => 'Teres Major and Minor',
                    'description' => 'Small muscles that assist in shoulder movement and stabilization, typically activated in pull-ups and rowing exercises.',
                    'translations' => [
                        ['locale' => 'es', 'column' => 'name', 'translation' => 'Teres Mayor y Menor'],
                        ['locale' => 'es', 'column' => 'description', 'translation' => 'Músculos pequeños que ayudan en el movimiento y la estabilización del hombro, típicamente activados en dominadas y ejercicios de remo.'],
                    ],
                    'category' => 'Back',
                ],
                [
                    'name' => 'Infraspinatus',
                    'description' => 'Part of the rotator cuff, it helps in shoulder joint stability and is targeted during rear delt flyes and external rotation exercises.',
                    'translations' => [
                        ['locale' => 'es', 'column' => 'name', 'translation' => 'Infraespinoso'],
                        ['locale' => 'es', 'column' => 'description', 'translation' => 'Parte del manguito rotador, ayuda en la estabilidad de la articulación del hombro y se dirige durante las elevaciones laterales posteriores y los ejercicios de rotación externa.'],
                    ],
                    'category' => 'Back',
                ],
                [
                    'name' => 'Deltoids',
                    'description' => 'The primary muscles in the shoulder, responsible for arm rotation and lifting. They have three parts: anterior (front), lateral (side), and posterior (rear).',
                    'translations' => [
                        ['locale' => 'es', 'column' => 'name', 'translation' => 'Deltoides'],
                        ['locale' => 'es', 'column' => 'description', 'translation' => 'Los músculos principales del hombro, responsables de la rotación y elevación del brazo. Tienen tres partes: anterior (frontal), lateral (lateral) y posterior (trasera).'],
                    ],
                    'category' => 'Shoulders',
                ],
                [
                    'name' => 'Rotator Cuff Muscles',
                    'description' => 'A group of four small muscles (supraspinatus, infraspinatus, teres minor, subscapularis) that stabilize the shoulder joint and assist in various arm movements.',
                    'translations' => [
                        ['locale' => 'es', 'column' => 'name', 'translation' => 'Músculos del Manguito Rotador'],
                        ['locale' => 'es', 'column' => 'description', 'translation' => 'Un grupo de cuatro músculos pequeños (supraespinoso, infraespinoso, redondo menor, subescapular) que estabilizan la articulación del hombro y ayudan en varios movimientos del brazo.'],
                    ],
                    'category' => 'Shoulders',
                ],
                [
                    'name' => 'Trapezius',
                    'description' => 'Extends from the neck to the middle of the back, aiding in shoulder blade movement and support.',
                    'translations' => [
                        ['locale' => 'es', 'column' => 'name', 'translation' => 'Trapecio'],
                        ['locale' => 'es', 'column' => 'description', 'translation' => 'Se extiende desde el cuello hasta la mitad de la espalda, ayudando en el movimiento y soporte de la escápula.'],
                    ],
                    'category' => 'Shoulders',
                ],
            ];

            foreach ($subcategories as $subcategoryData) {
                $translations = $subcategoryData['translations'];
                unset($subcategoryData['translations']);

                $category = $subcategoryData['category'];
                unset($subcategoryData['category']);

                // Get the category id using the category name
                $categoryId = DB::table('categories')->where('name', $category)->value('id');

                // Create the subcategory with the category id
                $subcategory = Subcategory::factory()->create(array_merge(
                    $subcategoryData,
                    [
                        'category_id' => $categoryId
                    ]
                ));

                foreach ($translations as $translationData) {
                    $subcategory->translations()->create($translationData);
                }
            }
        });
    }
}
