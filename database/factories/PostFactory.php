<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->unique()->sentence(5),
            'content' => fake()->paragraphs(3, true),
            'published_at' => fake()->dateTimeBetween('-1 year', 'now')
                ->format('c'),
        ];
    }

    /**
     * State para posts con imagen por defecto
     */
    public function withDefaultImage(): static
    {
        return $this->afterCreating(function ($post) {
            $this->createDefaultImage($post);
        });
    }

    /**
     * State para posts sin imagen
     */
    public function withoutImage(): static
    {
        return $this->afterCreating(function ($_) {
            // Do nothing, no image will be added
        });
    }

    /**
     * State para posts con imagen personalizada
     */
    public function withCustomImage(string $filename, int $width = 200, int $height = 200): static
    {
        return $this->afterCreating(
            function ($post) use ($filename, $width, $height) {
                $imageName = $filename ?? 'custom_post_image_' . $post->id . '.webp';

                $file = UploadedFile::fake()->image(
                    $imageName,
                    $width,
                    $height
                )->size(100);

                $file->mimeType('image/webp');

                $post->addMedia($file)
                    ->preservingOriginal()
                    ->toMediaCollection('images');
            }
        );
    }

    /**
     * Create default image for the post
     */
    private function createDefaultImage($post): void
    {
        $file = UploadedFile::fake()->image(
            'post_image_' . $post->id . '.webp',
            200,
            200
        )->size(100);

        $file->mimeType('image/webp');

        $post->addMedia($file)
            ->preservingOriginal()
            ->toMediaCollection('images');
    }
}
