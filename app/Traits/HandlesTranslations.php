<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

trait HandlesTranslations
{
    /**
     * Handle translations for a given model.
     *
     * @param Model $model
     * @param array $translations
     */
    private function handleTranslations(Model $model, array $translations): void
    {
        foreach ($translations as $translationData) {
            $model->translations()->create($translationData);
        }
    }
}
