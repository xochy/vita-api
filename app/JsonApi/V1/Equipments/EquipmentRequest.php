<?php

namespace App\JsonApi\V1\Equipments;

use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class EquipmentRequest extends ResourceRequest
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
                'max:100',
                Rule::unique('equipments', 'name')->ignore($this->route('equipment')),
            ],
            'description' => [
                'required',
                'string',
                'max:1000',
            ],
        ];
    }

}
