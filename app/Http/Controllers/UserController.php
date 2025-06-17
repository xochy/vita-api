<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Responses\TokenResponse;
use App\Models\User;
use App\Services\FirebaseService;
use App\Services\TokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;
use LaravelJsonApi\Core\Exceptions\JsonApiException;
use LaravelJsonApi\Laravel\Http\Controllers\Actions;
use Spatie\Permission\Models\Role;

class UserController extends Controller
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

    private $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Sign in the user. This method is called when the user tries to sign in.
     * It checks if the user exists and if the password is correct. If the user
     * does not exist or the password is incorrect, it throws a validation exception.
     *
     * @param Request $request
     *
     * @return TokenResponse
     */
    public function signin(Request $request, TokenService $tokenService): TokenResponse
    {
        $fields = $this->validateSignInFields($request);
        $validator = $this->makeSignInValidator($fields);

        if ($validator->stopOnFirstFailure()->fails()) {
            throw JsonApiException::error(
                [
                    'status' => 400, // Wrong request
                    'detail' => $validator->errors()->first()
                ]
            );
        }

        try {
            $user = User::where('email', $fields['email'])->firstOrFail();
        } catch (\Exception $e) {
            throw JsonApiException::error(
                [
                    'status' => 400, // Wrong request
                    'detail' => __('auth.failed')
                ]
            );
        }

        if (!Hash::check($fields['password'], optional($user)->password)) {
            throw JsonApiException::error(
                [
                    'status' => 400, // Wrong request
                    'detail' => __('auth.failed')
                ]
            );
        }

        $tokenData = $tokenService->createToken(
            $user,
            $request->input($fields['device_name'], 'Unknown Device'),
            $user->getAllPermissions()->pluck('name')->toArray()
        );

        return new TokenResponse($user, $tokenData->plainTextToken);
    }

    /**
     * Refresh the token. This method is called when the user tries to refresh the token.
     * It checks if the token is present in the request. If the token is missing, it throws
     * a validation exception.
     *
     * @param Request $request
     *
     * @return TokenResponse
     */
    public function refresh(Request $request): TokenResponse
    {
        $fields = $this->validateTokenVerificationFields($request);
        $validator = $this->makeTokenVerificationValidator($fields);

        if ($validator->stopOnFirstFailure()->fails()) {
            throw JsonApiException::error(
                [
                    'status' => 400, // Wrong request
                    'detail' => $validator->errors()->first()
                ]
            );
        }

        try {
            $token = PersonalAccessToken::findToken($fields['token']);
            $user = $token->tokenable;
        } catch (\Exception $e) {
            throw JsonApiException::error(
                [
                    'status' => 400, // Wrong request
                    'detail' => __('auth.token_refresh_failed')
                ]
            );
        }

        return new TokenResponse($user, $fields['token']);
    }

    /**
     * Make a validator for the sign in request. This method is called when the user tries to sign in.
     * It checks if the required fields are present in the request. If any of the required fields are
     * missing, it throws a validation exception.
     *
     * @param array $fields
     *
     * @return \Illuminate\Validation\Validator
     */
    private function makeSignInValidator(array $fields): \Illuminate\Validation\Validator
    {
        return Validator::make(
            $fields,
            [
                'email' => ['required', 'email'],
                'device_name' => ['required', 'string'],
                'password' => ['required', 'string'],
            ]
        );
    }

    /**
     * Verify the token. This method is called when the user tries to verify the token.
     * It checks if the token is present in the request. If the token is missing, it throws
     * a validation exception.
     *
     * @param Request $request
     *
     * @return \Illuminate\Validation\Validator
     */
    private function makeTokenVerificationValidator(array $fields): \Illuminate\Validation\Validator
    {
        return Validator::make(
            $fields,
            [
                'token' => ['required', 'string'],
            ]
        );
    }

    /**
     * Validate the login request. This method is called when the user tries to sign in.
     * It checks if the required fields are present in the request. If any of the required
     * fields are missing, it throws a validation exception.
     *
     * @param Request $request
     *
     * @return array
     */
    private function validateSignInFields(Request $request): array
    {
        try {
            $email = $request->data['attributes']['email'];
            $deviceName = $request->data['attributes']['device_name'];
            $password = $request->data['attributes']['password'];
        } catch (\Exception $e) {
            throw JsonApiException::error(
                [
                    'status' => 400, // Wrong request
                    'detail' => __('auth.required')
                ]
            );
        }

        return [
            'email' => $email,
            'device_name' => $deviceName,
            'password' => $password,
        ];
    }

    /**
     * Validate the token verification request. This method is called when the user tries to verify the token.
     * It checks if the required fields are present in the request. If any of the required fields are missing,
     * it throws a validation exception.
     *
     * @param Request $request
     *
     * @return array
     */
    private function validateTokenVerificationFields(Request $request): array
    {
        try {
            $token = $request->data['attributes']['token'];
        } catch (\Exception $e) {
            throw JsonApiException::error(
                [
                    'status' => 400, // Wrong request
                    'detail' => __('auth.required')
                ]
            );
        }

        return [
            'token' => $token,
        ];
    }

    /**
     * Sign out the user. This method is called when the user tries to sign out.
     * It deletes the current access token from the database. If the token is
     * deleted successfully, it returns a success message.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function signout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(
            [
                'status' => 200,
                'token' => __('auth.token_deleted'),
            ]
        );
    }

    /**
     * Sign up the user. This method is called when the user tries to sign up.
     * It creates a new user with the given data. If the user is created
     * successfully, it returns a success message.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function signup(Request $request): JsonResponse
    {
        $validator = $this->makeSignUpValidator($request->data['attributes']);

        if ($validator->stopOnFirstFailure()->fails()) {
            throw JsonApiException::error(
                [
                    'status' => 422, // Unprocessable Entity
                    'detail' => $validator->errors()->first()
                ]
            );
        }

        User::create(
            [
                'name' => $request->data['attributes']['name'],
                'email' => $request->data['attributes']['email'],
                'password' => Hash::make($request->data['attributes']['password']),
            ]
        );

        return response()->json(
            [
                'status' => 201,
                'message' => __('auth.user_created'),
            ],
            201
        );
    }

    /**
     * Make a validator for the sign up request. This method is called when the user tries to sign up.
     * It checks if the required fields are present in the request. If any of the required fields are
     * missing, it throws a validation exception.
     *
     * @param array $fields
     *
     * @return \Illuminate\Validation\Validator
     */
    private function makeSignUpValidator(array $fields): \Illuminate\Validation\Validator
    {
        return Validator::make(
            $fields,
            [
                'name' => ['required', 'string'],
                'email' => ['required', 'email', 'unique:users,email'],
                'password' => ['required', 'confirmed'],
                'password_confirmation' => ['required'],
            ]
        );
    }

    public function saved(User $user, Request $request): void
    {
        if (!isset($request->data['relationships']['roles']['data'])) {
            return;
        }

        $data = $request->data['relationships']['roles']['data'];
        $roleIds = array_column($data, 'id');
        $rolesNames = Role::whereIn('id', $roleIds)->pluck('name')->toArray();

        $user->syncRoles($rolesNames);
    }

    public function socialSignin(Request $request): TokenResponse
    {
        $fields = $this->validateSocialSignInFields($request);
        $validator = $this->makeSocialSignInValidator($fields);

        if ($validator->stopOnFirstFailure()->fails()) {
            throw JsonApiException::error(
                [
                    'status' => 422, // Unprocessable Entity
                    'detail' => $validator->errors()->first()
                ]
            );
        }

        try {
            // Verify Firebase token
            $firebaseUser = $this->firebaseService->verifyIdToken(
                $fields['firebase_token']
            );

            // Find or create user
            $user = $this->findOrCreateUser($firebaseUser, $fields['provider']);
        } catch (\Exception $e) {
            throw JsonApiException::error(
                [
                    'status' => 422, // Unprocessable Entity
                    'detail' => __('exceptions.invalid_firebase_token')
                ]
            );
        }

        return new TokenResponse($user);
    }

    private function findOrCreateUser($firebaseUser, $provider)
    {
        // Try to find user by Firebase UID first
        $user = User::where('firebase_uid', $firebaseUser['uid'])->first();

        if ($user) {
            return $user;
        }

        // Try to find by email
        $user = User::where('email', $firebaseUser['email'] ?? null)->first();

        if ($user) {
            // Link Firebase UID to existing user
            $user->update(['firebase_uid' => $firebaseUser['uid']]);
            return $user;
        }

        // Create new user
        return User::create([
            'name' => $firebaseUser['name'] ?? $firebaseUser['email'],
            'email' => $firebaseUser['email'],
            'firebase_uid' => $firebaseUser['uid'],
            'provider' => $provider,
            'email_verified_at' => $firebaseUser['email_verified'] ? now() : null,
            'avatar' => $firebaseUser['picture'] ?? null,
        ]);
    }

    private function validateSocialSignInFields(Request $request): array
    {
        try {
            $firebaseToken = $request->data['attributes']['firebase_token'];
            $provider = $request->data['attributes']['provider'];
        } catch (\Exception $e) {
            throw JsonApiException::error(
                [
                    'status' => 400, // Wrong request
                    'detail' => __('auth.required')
                ]
            );
        }

        return [
            'firebase_token' => $firebaseToken,
            'provider' => $provider,
        ];
    }

    private function makeSocialSignInValidator(array $fields): \Illuminate\Validation\Validator
    {
        return Validator::make(
            $fields,
            [
                'firebase_token' => ['required', 'string'],
                'provider' => ['required', 'string'],
            ]
        );
    }
}
