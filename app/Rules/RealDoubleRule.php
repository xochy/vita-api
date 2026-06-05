<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RealDoubleRule implements ValidationRule
{
    protected int $decimalPlaces;
    protected float $min;
    protected float $max;

    /**
     * Create a new rule instance.
     *
     * @param  int  $decimalPlaces
     * @param  float  $min
     * @param  float  $max
     * @return void
     */
    public function __construct(int $decimalPlaces, float $min, float $max)
    {
        $this->decimalPlaces = $decimalPlaces;
        $this->min = $min;
        $this->max = $max;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Validate if the value is a float or an integer
        if (!is_float($value) && !is_int($value)) {
            $fail(__('validation.real_double', [
                'attribute' => __('validation.attributes.' . $attribute)
            ]));

            return;
        }

        // Validate if the value is within the min and max range
        if ($value < $this->min || $value > $this->max) {
            $fail(__('validation.real_double_range', [
                'attribute' => __('validation.attributes.' . $attribute), 'min' => $this->min, 'max' => $this->max
            ]));

            return;
        }

        // If the value is float, validate that it has the correct number of decimal places
        if (is_float($value)) {
            $value = explode('.', $value);
            if (strlen($value[1]) > $this->decimalPlaces) {
                $fail(__('validation.real_double_decimal_places', [
                    'attribute' => __('validation.attributes.' . $attribute), 'decimalPlaces' => $this->decimalPlaces
                ]));
            }
        }
    }
}
