<?php

namespace App\JsonApi\V1\Variations;

use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class VariationRequest extends ResourceRequest
{

    /**
     * Get the validation rules for the resource.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'performance' => [
                'required',
                'string',
                'max:2048',
            ],
            'workout' => [
                JsonApiRule::toOne()
            ],
            'muscles' => [
                JsonApiRule::toMany()
            ],
        ];
    }

}
