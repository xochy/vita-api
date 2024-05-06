<?php

namespace App\JsonApi\V1\Subcategories;

use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class SubcategoryRequest extends ResourceRequest
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
                Rule::unique('subcategories', 'name')->ignore($this->route('subcategory')),
            ],
            'description' => [
                'required',
                'string',
                'max:1000',
            ],
            'category' => [
                JsonApiRule::toOne()
            ],
            'tranlations' => [
                JsonApiRule::toMany()
            ],


        ];
    }

}
