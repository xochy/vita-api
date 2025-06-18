<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RangeFormatRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Verificar que sea una cadena
        if (!is_string($value)) {
            $fail(__('validation.string', [
                'attribute' => __('validation.attributes.' . $attribute)
            ]));
            return;
        }

        // Patrón regex para validar la estructura
        // Acepta: número–número seguido opcionalmente de espacio y unidad (seg, min)
        $pattern = '/^(\d+[–\-]\d+|\d+\s+(seg|min))(\s+(seg|min))?$/';

        if (!preg_match($pattern, $value)) {
            $fail(__('validation.range_format', [
                'attribute' => __('validation.attributes.' . $attribute)
            ]));
        }
    }
}
