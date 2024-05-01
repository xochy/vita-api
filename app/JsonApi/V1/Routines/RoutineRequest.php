<?php

namespace App\JsonApi\V1\Routines;

use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class RoutineRequest extends ResourceRequest
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
            'plans' => [
                JsonApiRule::toMany()
            ],
            'workouts' => [
                JsonApiRule::toMany()
            ],
            'workouts.*.meta.pivot.series' => [
                'required',
                'integer',
                'numeric'
            ],
            'workouts.*.meta.pivot.repetitions' => [
                'required',
                'integer',
                'numeric'
            ],
            'workouts.*.meta.pivot.time' => [
                'required',
                'integer',
                'numeric'
            ],
        ];
    }

}
