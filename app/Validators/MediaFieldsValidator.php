<?php

namespace App\Validators;

use App\Rules\ExistsInTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MediaFieldsValidator
{
    /**
     * Get the validation rules for the request.
     *
     * @param Request $request - the request object
     * @param bool $singleFile - whether the request is for a single file or multiple files
     *
     * @return \Illuminate\Validation\Validator
     */
    public function validator(Request $request, bool $singleFile = true): \Illuminate\Validation\Validator
    {
        $validations = $this->getCommonValidationRules();

        $validations = array_merge(
            $validations,
            $singleFile
            ? $this->getSingleFileValidationRules()
            : $this->getMultipleFilesValidationRules()
        );

        return Validator::make($request->data, $validations);
    }

    /**
     * Get common validation rules.
     *
     * @return array
     */
    private function getCommonValidationRules(): array
    {
        return [
            'type' => [
                'required',
                'string',
                'max:100'
            ]
        ];
    }

    /**
     * Get validation rules for a single file.
     *
     * @return array
     */
    private function getSingleFileValidationRules(): array
    {
        return [
            'id' => [
                'required',
                'string',
                'exists:media,id'
            ]
        ];
    }

    /**
     * Get validation rules for multiple files.
     *
     * @return array
     */
    private function getMultipleFilesValidationRules(): array
    {
        return [
            'ids' => [
                'required',
                'array',
                new ExistsInTable('media', 'id'),
            ],
            'ids.*' => [
                'string',
            ]
        ];
    }
}
