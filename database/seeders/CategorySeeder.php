<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            DB::table('categories')->delete();

            $categoriesJson = File::get(database_path('seeders/json/categories.json'));
            $categories = json_decode($categoriesJson, true);

            foreach ($categories as $categoryData) {
                $translations = $categoryData['translations'];
                unset($categoryData['translations']);

                $category = Category::factory($categoryData)->create();

                foreach ($translations as $translationData) {
                    $category->translations()->create($translationData);
                }
            }
        });
    }
}
