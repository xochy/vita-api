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
     * __construct
     *
     * @param User $user
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
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
        return response()->json([
            'status' => 200,
            'token' => $this->user->createToken(
                $request->data['attributes']['device_name'],
                $this->user->permissions->pluck('name')->toArray()
            )->plainTextToken,
        ]);
    }
}
