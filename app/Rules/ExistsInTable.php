<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class ExistsInTable implements ValidationRule
{
    protected string $table;
    protected string $column;

    /**
     * Create a new rule instance.
     *
     * @param string $table
     * @param string $column
     */
    public function __construct(string $table, string $column = 'id')
    {
        $this->table = $table;
        $this->column = $column;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_array($value)) {
            $fail('The ' . $attribute . ' must be an array.');
            return;
        }

        $missingIds = array_diff($value, DB::table($this->table)->whereIn($this->column, $value)->pluck($this->column)->toArray());

        if (!empty($missingIds)) {
            $fail('The following IDs do not exist in the ' . $this->table . ' table: ' . implode(', ', $missingIds));
        }
    }
}
