<?php

namespace App\JsonApi\V1\Posts;

use Illuminate\Validation\Rule;
use LaravelJsonApi\Laravel\Http\Requests\ResourceRequest;
use LaravelJsonApi\Validation\Rule as JsonApiRule;

class PostRequest extends ResourceRequest
{

    /**
     * Get the validation rules for the resource.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('posts', 'title')->ignore($this->route('post')),
            ],
            'content' => [
                'required',
                'string',
                'max:10000',
            ],
            'publishedAt' => [
                'nullable',
                JsonApiRule::dateTime(),
            ],
        ];
    }
}
