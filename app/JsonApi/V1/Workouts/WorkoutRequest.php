<?php

namespace App\JsonApi\V1\Workouts;

use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class WorkoutRequest extends ResourceRequest
{
    const MAX_LENGTH = 'max:2048';

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
                Rule::unique('workouts', 'name')->ignore($this->route('workout')),
            ],
            'performance' => [
                'required',
                'string',
                self::MAX_LENGTH,
            ],
            'comments' => [
                'string',
                self::MAX_LENGTH,
            ],
            'corrections' => [
                'string',
                self::MAX_LENGTH,
            ],
            'warnings' => [
                'string',
                self::MAX_LENGTH,
            ],
            'subcategory' => [
                JsonApiRule::toOne()
            ],
        ];
    }

}
