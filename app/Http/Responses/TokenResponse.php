<?php

namespace App\Http\Responses;

use App\Models\User;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TokenResponse implements Responsable
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var string
     */
    private $token;

    /**
     * __construct
     *
     * @param User $user
     * @return void
     */
    public function __construct(User $user, string $token = null)
    {
        $this->user = $user;
        $this->token = $token;
    }

    /**
     * Automatic response
     *
     * @param Request $request
     *
     * @return Request response
     */
    public function toResponse($request): JsonResponse
    {
        $permissions = $this->user->getAllPermissions()->pluck('name')->toArray();

        // If token exists, update the expiration date
        if ($this->token) {
            $this->user->tokens()->where('token', hash('sha256', $this->token))
                ->update(['expires_at' => now()->addDays(5)]);
        } else {
            $this->token = $this->user->createToken(
                $request->data['attributes']['device_name'],
                $permissions,
                now()->addDays(10)
            )->plainTextToken;
        }

        $permissionsPayload = encryptPayload(json_encode($permissions));

        return response()->json(
            [
                'status'      => 200,
                'token'       => $this->token,
                'name'        => $this->user->name,
                'email'       => $this->user->email,
                'permissions' => $permissionsPayload,
            ]
        );
    }
}
