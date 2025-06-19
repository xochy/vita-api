<?php

namespace App\Http\Requests;

use App\Traits\HandlesJsonApiValidation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProposeRoutineRequest extends FormRequest
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

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes()
    {
        return [
            'data.level' => __('validation.attributes.level'),
            'data.equipment_ids' => __('validation.attributes.equipment_ids'),
            'data.equipment_ids.*' => __('validation.attributes.equipment_id'),
            'data.muscle_ids' => __('validation.attributes.muscle_ids'),
            'data.muscle_ids.*' => __('validation.attributes.muscle_id'),
        ];
    }

    // Get the proposed data from the request
    public function getProposedData()
    {
        return $this->validated()['data'];
    }
}
