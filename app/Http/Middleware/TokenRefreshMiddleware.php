<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use LaravelJsonApi\Core\Exceptions\JsonApiException;
use Symfony\Component\HttpFoundation\Response;

class TokenRefreshMiddleware
{
    /**
     * Routes that should be excluded from token refresh logic
     */
    private array $excludedRoutes = [
        'v1.users.signin',
        'v1.users.signup',
        'v1.users.signout',
        'v1.users.socialSignin',
        'api.health',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Skip token refresh for excluded routes
        if ($request->routeIs($this->excludedRoutes)) {
            return $next($request);
        }

        $bearerToken = $request->bearerToken();

        if (!$bearerToken) {
            throw JsonApiException::error([
                'status' => 401,
                'detail' => __('auth.token_not_provided')
            ]);
        }

        $token = PersonalAccessToken::findToken($bearerToken);

        if (!$token) {
            throw JsonApiException::error([
                'status' => 401,
                'detail' => __('auth.invalid_token')
            ]);
        }

        // Check if token is expired
        if ($token->expires_at && $token->expires_at->isPast()) {
            $token->delete();
            throw JsonApiException::error([
                'status' => 401,
                'detail' => __('auth.token_expired')
            ]);
        }

        // Update last_used_at for activity tracking
        // Don't update expires_at here - let Sanctum handle it naturally
        if ($token->last_used_at === null || $token->last_used_at->isPast()) {
            $token->last_used_at = now();
            $token->save();
        }

        return $next($request);
    }
}
