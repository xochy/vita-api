<?php

namespace App\Services;

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\NewAccessToken;

class TokenService
{
    /**
     * Create a new token for the user
     */
    public function createToken(User $user, string $deviceName, array $abilities = ['*']): NewAccessToken
    {
        // Revoke existing tokens for the same device to prevent token accumulation
        $user->tokens()
            ->where('name', $deviceName)
            ->delete();

        $expirationDays = (int) config('sanctum.expiration_days', 7);

        return $user->createToken(
            $deviceName,
            $abilities,
            now()->addDays($expirationDays)
        );
    }

    /**
     * Refresh an existing token
     */
    public function refreshToken(string $tokenString): bool
    {
        $token = PersonalAccessToken::findToken($tokenString);

        if (!$token || ($token->expires_at && $token->expires_at->isPast())) {
            return false;
        }

        $expirationDays = (int) config('sanctum.expiration_days', 7);

        $token->expires_at = now()->addDays($expirationDays);
        $token->last_used_at = now();
        $token->save();

        return true;
    }

    /**
     * Revoke a specific token
     */
    public function revokeToken(string $tokenString): bool
    {
        $token = PersonalAccessToken::findToken($tokenString);

        if ($token) {
            $token->delete();
            return true;
        }

        return false;
    }

    /**
     * Revoke all user tokens
     */
    public function revokeAllUserTokens(User $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * Count expired tokens without deleting them
     */
    public function countExpiredTokens(): int
    {
        return PersonalAccessToken::where('expires_at', '<', now())->count();
    }

    /**
     * Clean up expired tokens (can be run via scheduled job)
     */
    public function cleanupExpiredTokens(): int
    {
        return PersonalAccessToken::where('expires_at', '<', now())->delete();
    }

    /**
     * Get token information
     */
    public function getTokenInfo(string $tokenString): ?array
    {
        $token = PersonalAccessToken::findToken($tokenString);

        if (!$token) {
            return null;
        }

        return [
            'id' => $token->id,
            'name' => $token->name,
            'abilities' => $token->abilities,
            'last_used_at' => $token->last_used_at,
            'expires_at' => $token->expires_at,
            'created_at' => $token->created_at,
            'is_expired' => $token->expires_at && $token->expires_at->isPast(),
        ];
    }
}
