<?php

namespace App\JsonApi\V1\Frequencies;

use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class FrequencyRequest extends ResourceRequest
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
                Rule::unique('frequencies', 'name')->ignore($this->route('frequency')),
            ],
            'description' => [
                'required',
                'string',
                'max:255',
            ],
        ];
    }

}
