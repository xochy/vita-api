<?php

namespace App\Http\Requests;

use App\Traits\HandlesJsonApiValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateRoutineRequest extends FormRequest
{
    use HandlesJsonApiValidation;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'data.name' => [
                'required',
                'string',
                'max:100'
            ],
            'data.user_id' => [
                'required',
                'integer',
                'exists:users,id'
            ],
            'data.gender' => [
                'required',
                'string',
                Rule::in(['male', 'female', 'other'])
            ],
            'data.age' => [
                'required',
                'integer',
                'min:13',
                'max:100'
            ],
            'data.goal' => [
                'required',
                'string',
                Rule::in(['lose weight', 'gain muscle', 'gain strength'])
            ],
            'data.workout_ids' => [
                'required',
                'array',
                'min:1',
                'max:10'
            ],
            'data.workout_ids.*' => [
                'integer',
                'distinct',
                'exists:workouts,id'
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'data.name' => __('validation.attributes.name'),
            'data.user_id' => __('validation.attributes.user_id'),
            'data.gender' => __('validation.attributes.gender'),
            'data.age' => __('validation.attributes.age'),
            'data.goal' => __('validation.attributes.goal'),
            'data.workout_ids' => __('validation.attributes.workout_ids'),
            'data.workout_ids.*' => __('validation.attributes.workout_id'),
        ];
    }

    // Get the generated data from the request
    public function getGeneratedData()
    {
        return $this->validated()['data'];
    }
}
