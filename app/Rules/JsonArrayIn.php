<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class JsonArrayIn implements ValidationRule
{
    private $allowedValues;
    private $allowDuplicates;

    /**
     * Create a new rule instance.
     *
     * @param array $allowedValues
     * @param bool $allowDuplicates
     */
    public function __construct(array $allowedValues, bool $allowDuplicates = false)
    {
        $this->allowedValues = $allowedValues;
        $this->allowDuplicates = $allowDuplicates;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $decodedValue = $this->decodeValue($value);

        $errorMessage = $this->validateDecodedValue($decodedValue);

        if ($errorMessage !== null) {
            $fail($errorMessage);
        }
    }

    /**
     * Decode the input value to an array.
     */
    private function decodeValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return $value;
        }

        $decodedValue = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decodedValue : null;
    }

    /**
     * Validate the decoded array value.
     */
    private function validateDecodedValue(mixed $decodedValue): ?string
    {
        if ($decodedValue === null) {
            return __('validation.json_decode_error', ['attribute' => ':attribute']);
        }

        if (!is_array($decodedValue)) {
            return __('validation.array', ['attribute' => ':attribute']);
        }

        if (!$this->allowDuplicates && count($decodedValue) !== count(array_unique($decodedValue))) {
            return __('validation.duplicated', ['attribute' => ':attribute']);
        }

        foreach ($decodedValue as $item) {
            if (!in_array($item, $this->allowedValues)) {
                return __('validation.allowed_values', [
                    'attribute' => ':attribute',
                    'values' => implode(', ', $this->allowedValues)
                ]);
            }
        }

        return null;
    }
}
