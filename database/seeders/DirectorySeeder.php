<?php

namespace Database\Seeders;

use App\Models\Directory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DirectorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->deleteExistingDirectories();
            $directories = $this->getDirectoriesFromJson();

            foreach ($directories as $directoryData) {
                $this->processDirectory($directoryData);
            }
        });
    }

    private function deleteExistingDirectories(): void
    {
        DB::table('directories')->delete();
    }

    private function getDirectoriesFromJson(): array
    {
        $directoriesJson = File::get(database_path('seeders/json/directories.json'));
        return json_decode($directoriesJson, true);
    }

    private function processDirectory(array $directoryData): void
    {
        if (!isset($directoryData['parent'])) {
            Directory::factory()->create(array_merge(
                $directoryData,
            ));
            return;
        }

        $parent = $directoryData['parent'];
        unset($directoryData['parent']);

        $parentId = $this->getParentIdByName($parent);

        Directory::factory()->create(array_merge(
            $directoryData,
            ['parent_id' => $parentId]
        ));
    }

    private function getParentIdByName(string $parentName): int
    {
        return DB::table('directories')
            ->where('name', $parentName)
            ->value('id');
    }
}
