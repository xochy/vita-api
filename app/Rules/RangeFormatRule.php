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
        $patterns = [
            '/^\d+\s*-\s*\d+\s*(seg|min)?$/', // 10-20, 10 - 20, 10-20 seg, 10 - 20 min
            '/^\d+\s*(seg|min)$/',            // 10 seg, 10 min
            '/^\d+[–]\d+$/',                  // 10–20 (en dash)
        ];

        $matched = false;
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                $matched = true;
                break;
            }
        }

        if (!$matched) {
            $fail(__('validation.range_format', [
                'attribute' => __('validation.attributes.' . $attribute)
            ]));
        }
    }
}
