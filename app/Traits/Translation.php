<?php

namespace App\Traits;

trait Translation
{
    /**
     * Get the translation of a column.
     *
     * @param string $column
     * @param string $default
     * @return string
     */
    public function translation($column, $default = '')
    {
        $locale = app()->getLocale();

        $translation = $this->translations()
            ->where([
                ['locale', $locale],
                ['column', $column]
            ])
            ->first();

        return $translation?->translation ?? $default;
    }
}
