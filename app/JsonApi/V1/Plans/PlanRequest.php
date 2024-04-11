<?php

namespace App\JsonApi\V1\Plans;

use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class PlanRequest extends ResourceRequest
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
            'goal' => [
                JsonApiRule::toOne(),
                'required'
            ],
            'frequency' => [
                JsonApiRule::toOne(),
                'required'
            ],
            'physicalCondition' => [
                JsonApiRule::toOne(),
                'required'
            ],
        ];
    }

}
