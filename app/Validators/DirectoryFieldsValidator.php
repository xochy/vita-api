<?php

namespace App\Validators;

use App\Rules\Base64Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DirectoryFieldsValidator
{
    /**
     * Get the validation rules for the request.
     *
     * @param Request $request - the request object
     * @param bool $singleFile - whether the request is for a single file or multiple files
     *
     * @return \Illuminate\Validation\Validator
     */
    public function generalMediaValidator(Request $request): \Illuminate\Validation\Validator
    {
        return Validator::make($request->data['meta']['media'], [
            'action' => [
                'required',
                'string',
                'max:6',
                'in:store,update,delete'
            ],
            'path' => [
                'required',
                'string',
            ]
        ]);
    }

    /**
     * Get the validation rules for the request when the media is base64 encoded.
     *
     * @param Request $request - the request object
     * @param bool $singleFile - whether the request is for a single file or multiple files
     *
     * @return \Illuminate\Validation\Validator
     */
    public function base64MediaValidator($media): \Illuminate\Validation\Validator
    {
        return Validator::make($media, [
            'content' => [
                'required',
                'string',
                new Base64Rule(),
            ],
            'filename' => [
                'required',
                'string',
                'max:255'
            ]
        ]);
    }

    /**
     * Get the validation rules for the request when the media is needed to be updated.
     *
     * @param Request $request - the request object
     * @param bool $singleFile - whether the request is for a single file or multiple files
     *
     * @return \Illuminate\Validation\Validator
     */
    public function updateMediaValidator($media): \Illuminate\Validation\Validator
    {
        $messages = [
            'attributes.filename.required' => (__(
                'validation.required',
                [
                    'attribute' => __('validation.attributes.filename')
                ]
            )),
            'attributes.filename.string' => (__(
                'validation.string',
                [
                    'attribute' => __('validation.attributes.filename')
                ]
            )),
            'attributes.filename.max' => (__(
                'validation.max.string',
                [
                    'attribute' => __('validation.attributes.filename'),
                    'max' => 255
                ]
            )),
        ];

        return Validator::make($media, [
            'id' => [
                'required',
                'string',
                'exists:media,id'
            ],
            'attributes.filename' => [
                'required',
                'string',
                'max:255'
            ]
        ], $messages);
    }

    /**
     * Get the validation rules for the request when the media is needed to be deleted.
     *
     * @param Request $request - the request object
     * @param bool $singleFile - whether the request is for a single file or multiple files
     *
     * @return \Illuminate\Validation\Validator
     */
    public function deleteMediaValidator($media): \Illuminate\Validation\Validator
    {
        return Validator::make($media, [
            'id' => [
                'required',
                'string',
                'exists:media,id'
            ]
        ]);
    }
}
