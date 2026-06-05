<?php

namespace App\JsonApi\V1\Routines;

use App\Rules\RangeFormatRule;
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
                new RangeFormatRule(),
            ],
            'workouts.*.meta.pivot.repetitions' => [
                'required',
                new RangeFormatRule(),
            ],
            'workouts.*.meta.pivot.time' => [
                'required',
                new RangeFormatRule(),
            ],
            'workouts.*.meta.pivot.rest' => [
                'required',
                new RangeFormatRule(),
            ],
        ];
    }

}
