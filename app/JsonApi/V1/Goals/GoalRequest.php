<?php

namespace App\JsonApi\V1\Goals;

use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class GoalRequest extends ResourceRequest
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
                Rule::unique('goals', 'name')->ignore($this->route('goal')),
            ],
            'description' => [
                'required',
                'string',
                'max:255',
            ],
        ];
    }

}
