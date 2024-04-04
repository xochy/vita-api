<?php

namespace App\JsonApi\V1\Muscles;

use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class MuscleRequest extends ResourceRequest
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
                Rule::unique('muscles', 'name')->ignore($this->route('muscle')),
            ],
            'description' => [
                'required',
                'string',
                'max:255',
            ],
            'workouts' => [
                JsonApiRule::toMany()
            ],
        ];
    }

}
