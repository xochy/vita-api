<?php

namespace Database\Seeders;

use App\Models\Comment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->deleteExistingComments();
            $comments = $this->getCommentsFromJson();

            foreach ($comments as $commentData) {
                $this->processComment($commentData);
            }

        });
    }

    private function deleteExistingComments(): void
    {
        DB::table('comments')->delete();
    }

    private function getCommentsFromJson(): array
    {
        $commentsJson = file_get_contents(database_path('seeders/json/comments.json'));
        return json_decode($commentsJson, true);
    }

    private function processComment(array $commentData): void
    {
        $commentData['user_id'] = $commentData['user']['id'];
        $commentData['post_id'] = $commentData['post']['id'];
        unset($commentData['user'], $commentData['post']);
        Comment::factory($commentData)->create();
    }
}
