<?php

namespace App\JsonApi\V1\PhysicalConditions;

use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class PhysicalConditionRequest extends ResourceRequest
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
                Rule::unique('physical_conditions', 'name')->ignore($this->route('physical_condition')),
            ],
            'description' => [
                'required',
                'string',
                'max:255',
            ],
        ];
    }

}
