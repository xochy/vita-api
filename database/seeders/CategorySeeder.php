<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Traits\HandlesTranslations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CategorySeeder extends Seeder
{
    use HandlesTranslations;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->deleteExistingCategories();
            $categories = $this->getCategoriesFromJson();

            foreach ($categories as $categoryData) {
                $this->processCategory($categoryData);
            }
        });
    }

    private function deleteExistingCategories(): void
    {
        DB::table('categories')->delete();
    }

    private function getCategoriesFromJson(): array
    {
        $categoriesJson = File::get(database_path('seeders/json/categories.json'));
        return json_decode($categoriesJson, true);
    }

    private function processCategory(array $categoryData): void
    {
        $translations = $categoryData['translations'];
        unset($categoryData['translations']);

        $category = Category::factory($categoryData)->create();
        $this->handleTranslations($category, $translations);
    }
}
