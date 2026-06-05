<?php

namespace App\JsonApi\V1\Translations;

use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class TranslationRequest extends ResourceRequest
{

    /**
     * Get the validation rules for the resource.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'locale' => [
                'required',
                'string',
                'max:3',
            ],
            'column' => [
                'required',
                'string',
                'max:30',
            ],
            'translation' => [
                'required',
                'string',
                'max:1000',
            ],
            'translationable' => JsonApiRule::toOne(),
        ];
    }

}
