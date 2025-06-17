<?php

namespace App\Http\Responses;

use App\Models\User;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

class TokenResponse implements Responsable
{
    private User $user;
    private ?string $token;

    public function __construct(User $user, ?string $token = null)
    {
        $this->user = $user;
        $this->token = $token;
    }

    /**
     * Create a new response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toResponse($request): JsonResponse
    {
        $permissions = $this->getUserPermissions();
        $this->token = $this->resolveToken($request, $permissions);

        return $this->buildJsonResponse($permissions);
    }

    /**
     * Get the permissions of the user.
     *
     * @return array
     */
    private function getUserPermissions(): array
    {
        return $this->user->getAllPermissions()->pluck('name')->toArray();
    }

    /**
     * Resolve the token for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $permissions
     * @return string
     */
    private function resolveToken(Request $request, array $permissions): string
    {
        if ($this->token) {
            return $this->refreshExistingToken($request, $permissions);
        }

        return $this->createNewToken($request, $permissions);
    }

    /**
     * Refresh an existing token or create a new one if it doesn't exist.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $permissions
     * @return string
     */
    private function refreshExistingToken(Request $request, array $permissions): string
    {
        $tokenModel = $this->findExistingTokenModel();

        if ($tokenModel) {
            $this->updateTokenExpiration($tokenModel);
            return $this->token;
        }

        // Token not found, create a new one
        return $this->createNewToken($request, $permissions);
    }

    /**
     * Find the existing token model for the user.
     *
     * @return PersonalAccessToken|null
     */
    private function findExistingTokenModel(): ?PersonalAccessToken
    {
        $tokenSecret = $this->extractTokenSecret();

        return $this->user->tokens()
            ->where('token', hash('sha256', $tokenSecret))
            ->first();
    }

    /**
     * Extract the token secret from the provided token.
     *
     * @return string
     */
    private function extractTokenSecret(): string
    {
        $tokenParts = explode('|', $this->token, 2);
        return $tokenParts[1] ?? $this->token; // fallback for old tokens
    }

    /**
     * Update the expiration and last used time of the token.
     *
     * @param  PersonalAccessToken  $tokenModel
     * @return void
     */
    private function updateTokenExpiration(PersonalAccessToken $tokenModel): void
    {
        $expirationDays = $this->getExpirationDays();

        $tokenModel->expires_at = now()->addDays($expirationDays);
        $tokenModel->last_used_at = now();
        $tokenModel->save();
    }

    /**
     * Create a new token for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $permissions
     * @return string
     */
    private function createNewToken(Request $request, array $permissions): string
    {
        $deviceName = $this->getDeviceName($request);
        $expirationDays = $this->getExpirationDays();

        return $this->user->createToken(
            $deviceName,
            $permissions,
            now()->addDays($expirationDays)
        )->plainTextToken;
    }

    /**
     * Get the device name from the request or use a default value.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    private function getDeviceName(Request $request): string
    {
        return $request->input('data.attributes.device_name', 'Unknown Device');
    }

    /**
     * Get the number of days until the token expires.
     *
     * @return int
     */
    private function getExpirationDays(): int
    {
        return (int) config('sanctum.expiration_days', 7);
    }

    /**
     * Build the JSON response for the token.
     *
     * @param  array  $permissions
     * @return \Illuminate\Http\JsonResponse
     */
    private function buildJsonResponse(array $permissions): JsonResponse
    {
        return response()->json([
            'status' => 200,
            'token' => $this->token,
            'name' => $this->user->name,
            'email' => $this->user->email,
            'permissions' => $this->encryptPermissions($permissions),
            'expires_at' => $this->getExpirationDateTime(),
        ]);
    }

    /**
     * Encrypt the permissions array for secure transmission.
     *
     * @param  array  $permissions
     * @return string
     */
    private function encryptPermissions(array $permissions): string
    {
        return encryptPayload(json_encode($permissions));
    }

    /**
     * Get the expiration date and time for the token.
     *
     * @return string
     */
    private function getExpirationDateTime(): string
    {
        $expirationDays = $this->getExpirationDays();
        return now()->addDays($expirationDays)->toISOString();
    }
}
