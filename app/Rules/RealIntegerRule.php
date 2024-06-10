<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RealIntegerRule implements ValidationRule
{
    protected int $min;
    protected int $max;

    /**
     * Create a new rule instance.
     *
     * @param  int  $min
     * @param  int  $max
     * @return void
     */
    public function __construct(int $min, int $max)
    {
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
        // Validate if the value is an integer
        if (!is_int($value)) {
            $fail(__('validation.real_integer', [
                'attribute' => __('validation.attributes.' . $attribute)
            ]));

            return;
        }

        // Validate if the value is within the min and max range
        if ($value < $this->min || $value > $this->max) {
            $fail(__('validation.real_integer_range', [
                'attribute' => __('validation.attributes.' . $attribute), 'min' => $this->min, 'max' => $this->max
            ]));
        }
    }
}
