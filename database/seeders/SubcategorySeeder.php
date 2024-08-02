<?php

namespace Database\Seeders;

use App\Models\Subcategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SubcategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            DB::table('subcategories')->delete();

            $subcategoriesJson = File::get(database_path('seeders/json/subcategories.json'));
            $subcategories = json_decode($subcategoriesJson, true);

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
