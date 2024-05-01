<?php

namespace App\JsonApi\V1\Users;

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
