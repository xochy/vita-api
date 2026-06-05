<?php

namespace Database\Factories\Traits;

use Illuminate\Http\UploadedFile;

trait HasImageStates
{
    /**
     * State para posts con imagen por defecto
     *
     * @param string|null $collectionName El nombre de la colección de medios (opcional).
     */
    public function withDefaultImage(?string $collectionName = null): static
    {
        return $this->afterCreating(function ($model) use ($collectionName) {
            $this->createDefaultImage($model, $collectionName);
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
     *
     * @param string $filename Nombre del archivo de la imagen.
     * @param int $width Ancho de la imagen.
     * @param int $height Alto de la imagen.
     * @param string|null $collectionName El nombre de la colección de medios (opcional).
     */
    public function withCustomImage(string $filename, int $width = 200, int $height = 200, ?string $collectionName = null): static
    {
        return $this->afterCreating(
            function ($model) use ($filename, $width, $height, $collectionName) {
                $imageName = $filename ?? $this->getDefaultImageName($model) . '.webp';

                $file = UploadedFile::fake()->image(
                    $imageName,
                    $width,
                    $height
                )->size(100);

                $file->mimeType('image/webp');

                $model->addMedia($file)
                    ->preservingOriginal()
                    ->toMediaCollection($collectionName ?? $this->getImageCollection());
            }
        );
    }

    /**
     * Create default image for the model
     *
     * @param mixed $model El modelo Eloquent.
     * @param string|null $collectionName El nombre de la colección de medios (opcional).
     */
    private function createDefaultImage($model, ?string $collectionName = null): void
    {
        $file = UploadedFile::fake()->image(
            $this->getDefaultImageName($model) . '.webp',
            $this->getDefaultImageWidth(),
            $this->getDefaultImageHeight()
        )->size(100);

        $file->mimeType('image/webp');

        $model->addMedia($file)
            ->preservingOriginal()
            ->toMediaCollection($collectionName ?? $this->getImageCollection());
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
