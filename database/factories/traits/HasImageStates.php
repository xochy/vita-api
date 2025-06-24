<?php

namespace Database\Factories\Traits;

use Illuminate\Http\UploadedFile;

trait HasImageStates
{
    /**
     * State para posts con imagen por defecto
     */
    public function withDefaultImage(): static
    {
        return $this->afterCreating(function ($model) {
            $this->createDefaultImage($model);
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
            function ($model) use ($filename, $width, $height) {
                $imageName = $filename ?? $this->getDefaultImageName($model) . '.webp';

                $file = UploadedFile::fake()->image(
                    $imageName,
                    $width,
                    $height
                )->size(100);

                $file->mimeType('image/webp');

                $model->addMedia($file)
                    ->preservingOriginal()
                    ->toMediaCollection($this->getImageCollection());
            }
        );
    }

    /**
     * Create default image for the model
     */
    private function createDefaultImage($model): void
    {
        $file = UploadedFile::fake()->image(
            $this->getDefaultImageName($model) . '.webp',
            $this->getDefaultImageWidth(),
            $this->getDefaultImageHeight()
        )->size(100);

        $file->mimeType('image/webp');

        $model->addMedia($file)
            ->preservingOriginal()
            ->toMediaCollection($this->getImageCollection());
    }

    /**
     * Get the default image name for the model
     */
    protected function getDefaultImageName($model): string
    {
        $modelName = strtolower(class_basename($model));
        return $modelName . '_image_' . $model->id;
    }

    /**
     * Get the default image width
     */
    protected function getDefaultImageWidth(): int
    {
        return 300;
    }

    /**
     * Get the default image height
     */
    protected function getDefaultImageHeight(): int
    {
        return 200;
    }

    /**
     * Get the media collection name for images
     */
    protected function getImageCollection(): string
    {
        return 'images';
    }
}
