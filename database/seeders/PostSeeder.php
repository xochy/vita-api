<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Traits\HandlesTranslations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PostSeeder extends Seeder
{
    use HandlesTranslations;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->deleteExistingPosts();
            $categories = $this->getPostsFromJson();

            foreach ($categories as $categoryData) {
                $this->processPost($categoryData);
            }
        });
    }

    private function deleteExistingPosts(): void
    {
        DB::table('posts')->delete();
    }

    private function getPostsFromJson(): array
    {
        $postsJson = file_get_contents(database_path('seeders/json/posts.json'));
        return json_decode($postsJson, true);
    }

    private function processPost(array $postData): void
    {
        $translations = $postData['translations'];
        unset($postData['translations']);

        $postData['user_id'] = $postData['user']['id'];
        unset($postData['user']);

        $post = Post::factory($postData)
            ->withCustomImage('post_image.webp', 300, 300)
            ->create();

        $this->handleTranslations($post, $translations);
    }
}
