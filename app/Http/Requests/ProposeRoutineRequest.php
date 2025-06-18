<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProposeRoutineRequest extends FormRequest
{
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
            'data' => [
                'required',
                'array',
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
            'data.level' => [
                'required',
                'string',
                Rule::in(['beginner', 'intermediate', 'advanced'])
            ],
            'data.equipment_ids' => [
                'required',
                'array',
                'min:1',
                'max:20'
            ],
            'data.equipment_ids.*' => [
                'integer',
                'distinct',
                'exists:equipments,id'
            ],
            'data.muscle_ids' => [
                'required',
                'array',
                'min:1',
                'max:15'
            ],
            'data.muscle_ids.*' => [
                'integer',
                'distinct',
                'exists:muscles,id'
            ],
        ];
    }


    public function messages()
    {
        return [
            'data.user_id.exists' => __('validation.user_not_exists'),
            'data.gender.in' => __('validation.gender_invalid'),
            'data.age.min' => __('validation.age_min'),
            'data.age.max' => __('validation.age_max'),
            'data.goal.in' => __('validation.goal_invalid'),
            'data.level.in' => __('validation.level_invalid'),
            'data.equipment_ids.required' => __('validation.equipment_required'),
            'data.equipment_ids.*.exists' => __('validation.equipment_not_exists'),
            'data.equipment_ids.*.distinct' => __('validation.equipment_duplicate'),
            'data.muscle_ids.required' => __('validation.muscle_required'),
            'data.muscle_ids.*.exists' => __('validation.muscle_not_exists'),
            'data.muscle_ids.*.distinct' => __('validation.muscle_duplicate')
        ];
    }

    // Método para obtener solo los datos validados de 'data'
    public function getRoutineData()
    {
        return $this->validated()['data'];
    }
}
