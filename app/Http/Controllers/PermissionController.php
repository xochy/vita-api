<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use LaravelJsonApi\Core\Exceptions\JsonApiException;
use LaravelJsonApi\Laravel\Http\Controllers\Actions;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    use Actions\FetchMany;
    use Actions\FetchOne;
    use Actions\Store;
    use Actions\Update;
    use Actions\Destroy;
    use Actions\FetchRelated;
    use Actions\FetchRelationship;
    use Actions\UpdateRelationship;
    use Actions\AttachRelationship;
    use Actions\DetachRelationship;

    /**
     * Create a new permission with the given values.
     *
     * @param Request $request
     *
     * @return void
     */
    public function createPermission(Request $request): JsonResponse
    {
        if ($request->user()->cannot('create permissions')) {
            throw JsonApiException::error(
                [
                    'status' => 403, // Forbidden
                    'detail' => __('permissions.cannot_create')
                ]
            );
        }

        $fields = $this->validatePermissionFields($request, false);
        $validator = $this->makePermissionFiledsValidator($fields, false);

        if ($validator->stopOnFirstFailure()->fails()) {
            throw JsonApiException::error(
                [
                    'status' => 400, // Wrong request
                    'detail' => $validator->errors()->first()
                ]
            );
        }

        Permission::create(
            [
                'name'         => $fields['name'],
                'display_name' => $fields['display_name'],
                'action'       => $fields['action'],
                'subject'      => $fields['subject']
            ]
        );

        return response()->json(null, 201); // Created
    }

    /**
     * Update the permission with the given values.
     *
     * @param Request $request
     *
     * @return void
     */
    public function updatePermission(Request $request): JsonResponse
    {
        if ($request->user()->cannot('update permissions')) {
            throw JsonApiException::error(
                [
                    'status' => 403, // Forbidden
                    'detail' => __('permissions.cannot_update')
                ]
            );
        }

        $fields = $this->validatePermissionFields($request);
        $validator = $this->makePermissionFiledsValidator($fields);

        if ($validator->stopOnFirstFailure()->fails()) {
            throw JsonApiException::error(
                [
                    'status' => 400, // Wrong request
                    'detail' => $validator->errors()->first()
                ]
            );
        }

        $permission = Permission::find($fields['id']);

        $permission->update(
            [
                'name'         => $fields['name'],
                'display_name' => $fields['display_name'],
                'action'       => $fields['action'],
                'subject'      => $fields['subject']
            ]
        );

        return response()->json(null, 200); // Success
    }

    /**
     * Delete the permission with the given id.
     *
     * @param Request $request
     *
     * @return void
     */
    public function deletePermission(Request $request): JsonResponse
    {
        if ($request->user()->cannot('delete permissions')) {
            throw JsonApiException::error(
                [
                    'status' => 403, // Forbidden
                    'detail' => __('permissions.cannot_delete')
                ]
            );
        }

        $permission = Permission::find(array_key_first($request->query()));

        if (!$permission) {
            throw JsonApiException::error(
                [
                    'status' => 404, // Not found
                    'detail' => __('permissions.not_found')
                ]
            );
        }

        try {
            $permission->delete();
            return response()->json(null, 204); // No Content
        } catch (\Exception $e) {
            throw JsonApiException::error(
                [
                    'status' => 400, // Wrong request
                    'detail' => __('permissions.delete_failed')
                ]
            );
        }
    }

    /**
     * Validate if the request has the required fields. If not, it throws an exception.
     *
     * @param Request $request
     *
     * @return array
     */
    private function validatePermissionFields(Request $request, bool $withId = true): array
    {
        try {
            if ($withId) {
                $id = $request->data['id'];
            }

            $name        = $request->data['attributes']['name'];
            $displayName = $request->data['attributes']['display_name'];
            $action      = $request->data['attributes']['action'];
            $subject     = $request->data['attributes']['subject'];
        } catch (\Exception $e) {
            throw JsonApiException::error(
                [
                    'status' => 400, // Wrong request
                    'detail' => __('permissions.required_params')
                ]
            );
        }

        $values = [
            'name'         => $name,
            'display_name' => $displayName,
            'action'       => $action,
            'subject'      => $subject
        ];

        if ($withId) {
            $values['id'] = $id;
        }

        return $values;
    }

    /**
     * Make the validator for the update role fields.
     *
     * @param array $fields
     *
     * @return \Illuminate\Validation\Validator
     */
    private function makePermissionFiledsValidator(array $fields, bool $withId = true): \Illuminate\Validation\Validator
    {
        $validations = [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'name')
            ],
            'display_name' => [
                'required',
                'string'
            ],
            'action' => [
                'required',
                'string'
            ],
            'subject' => [
                'required',
                'string'
            ],
        ];

        if ($withId) {
            $validations['id'] = [
                'required',
                'integer',
                Rule::exists('roles', 'id')
            ];
        }

        return Validator::make(
            $fields,
            $validations
        );
    }
}
