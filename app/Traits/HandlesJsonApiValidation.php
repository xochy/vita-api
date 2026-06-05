<?php

namespace App\Traits;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

trait HandlesJsonApiValidation
{
    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = [];

        foreach ($validator->errors()->messages() as $field => $messages) {
            $pointer = $this->convertFieldToPointer($field);

            foreach ($messages as $message) {
                $errors[] = [
                    'status' => '400',
                    'source' => ['pointer' => $pointer],
                    'detail' => $message
                ];
            }
        }

        throw new HttpResponseException(
            response()
                ->json(['errors' => $errors], 400)
                ->header('Content-Type', 'application/vnd.api+json')
        );
    }

    /**
     * Convert a field name to a JSON:API pointer.
     */
    protected function convertFieldToPointer(string $field): string
    {
        $cleanField = str_replace('data.', '', $field);
        $cleanField = str_replace('.', '/', $cleanField);
        $cleanField = str_replace('/*', '', $cleanField);

        return "/data/attributes/{$cleanField}";
    }
}
