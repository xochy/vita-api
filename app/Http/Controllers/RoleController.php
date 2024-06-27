<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use LaravelJsonApi\Core\Exceptions\JsonApiException;
use LaravelJsonApi\Laravel\Http\Controllers\Actions;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\JsonResponse;

class RoleController extends Controller
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
     * Create a new role with the given values.
     *
     * @param Request $request
     *
     * @return void
     */
    public function createRole(Request $request): JsonResponse
    {
        if ($request->user()->cannot('create roles')) {
            throw JsonApiException::error(
                [
                    'status' => 403, // Forbidden
                    'detail' => __('roles.cannot_create')
                ]
            );
        }

        $fields = $this->validateRoleFields($request, false);
        $validator = $this->makeRoleFiledsValidator($fields, false);

        if ($validator->stopOnFirstFailure()->fails()) {
            throw JsonApiException::error(
                [
                    'status' => 400, // Wrong request
                    'detail' => $validator->errors()->first()
                ]
            );
        }

        Role::create(
            [
                'name'         => $fields['name'],
                'display_name' => $fields['display_name'],
                'default'      => false
            ]
        );

        return response()->json(null, 201); // Created
    }

    /**
     * Update the role with the new values. If the role does not exist, it throws an exception.
     *
     * @param Request $request
     *
     * @return void
     */
    public function updateRole(Request $request): void
    {
        if ($request->user()->cannot('update roles')) {
            throw JsonApiException::error(
                [
                    'status' => 403, // Forbidden
                    'detail' => __('roles.cannot_update')
                ]
            );
        }

        $fields = $this->validateRoleFields($request);
        $validator = $this->makeRoleFiledsValidator($fields);

        if ($validator->stopOnFirstFailure()->fails()) {
            throw JsonApiException::error(
                [
                    'status' => 400, // Wrong request
                    'detail' => $validator->errors()->first()
                ]
            );
        }

        $role = Role::find($fields['id']);

        $role->name = $fields['name'];
        $role->display_name = $fields['display_name'];

        try {
            $role->update();
        } catch (\Exception $e) {
            throw JsonApiException::error(
                [
                    'status' => 400, // Wrong request
                    'detail' => __('roles.update_failed')
                ]
            );
        }
    }

    /**
     * Delete the role with the given id. If the role does not exist, it throws an exception.
     *
     * @param Request $request
     * @param int $id
     *
     * @return void
     */
    public function deleteRole(Request $request): JsonResponse
    {
        if ($request->user()->cannot('delete roles')) {
            throw JsonApiException::error(
                [
                    'status' => 403, // Forbidden
                    'detail' => __('roles.cannot_delete')
                ]
            );
        }

        $role = Role::find(array_key_first($request->query()));

        if (!$role) {
            throw JsonApiException::error(
                [
                    'status' => 404, // Not found
                    'detail' => __('roles.not_found')
                ]
            );
        }

        try {
            $role->delete();
            return response()->json(null, 204); // No Content
        } catch (\Exception $e) {
            throw JsonApiException::error(
                [
                    'status' => 400, // Wrong request
                    'detail' => __('roles.delete_failed')
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
    private function validateRoleFields(Request $request, bool $withId = true): array
    {
        try {
            if ($withId) {
                $id = $request->data['id'];
            }

            $name = $request->data['attributes']['name'];
            $displayName = $request->data['attributes']['display_name'];
        } catch (\Exception $e) {
            throw JsonApiException::error(
                [
                    'status' => 400, // Wrong request
                    'detail' => __('roles.required_params')
                ]
            );
        }

        $values = [
            'name' => $name,
            'display_name' => $displayName
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
    private function makeRoleFiledsValidator(array $fields, bool $withId = true): \Illuminate\Validation\Validator
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
