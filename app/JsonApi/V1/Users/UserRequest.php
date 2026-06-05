<?php

namespace App\JsonApi\V1\Users;

use App\Enums\GenderEnum;
use App\Enums\MeasurementSystemEnum;
use App\Rules\RealDoubleRule;
use App\Rules\RealIntegerRule;
use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class UserRequest extends ResourceRequest
{
    /**
     * Get the validation rules for the resource.
     *
     * @return array
     */
    public function rules(): array
    {
        // Model instance
        $model = $this->model();

        $minWeight = app()->getLocale() === 'es' ? 10 : 50;
        $maxWeight = app()->getLocale() === 'es' ? 200 : 400;

        $minHeight = app()->getLocale() === 'es' ? 0.5 : 1.5;
        $maxHeight = app()->getLocale() === 'es' ? 2.5 : 8.5;

        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->route('user')),
            ],
            'email_verified_at' => [
                'sometimes',
                'date',
            ],
            // If the model exists, the password is not required
            'password' => [
                $model ? 'filled' : 'required',
                'string',
                'min:8',
            ],
            'age' => [
                'nullable',
                new RealIntegerRule(10, 80),
            ],
            'gender' => [
                'nullable',
                'string',
                Rule::in(GenderEnum::getAllValues()),
            ],
            'system' => [
                'nullable',
                'string',
                Rule::in(MeasurementSystemEnum::getAllValues()),
            ],
            'weight' => [
                'nullable',
                new RealDoubleRule(2, $minWeight, $maxWeight),
            ],
            'height' => [
                'nullable',
                new RealDoubleRule(2, $minHeight, $maxHeight),
            ],
            'plans' => [
                JsonApiRule::toMany()
            ],
            'deletedAt' => [
                'nullable',
                JsonApiRule::dateTime()
            ],
        ];

        // when creating, we do expect the password confirmation to always exist
        if (!$model) {
            $rules['password_confirmation'] = 'required_with:password|same:password';
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        if ($this->isUpdating()) {
            $validator->sometimes(
                'password_confirmation',
                'required_with:password|same:password',
                fn ($input) => isset($input['password']),
            );
        }
    }
}
