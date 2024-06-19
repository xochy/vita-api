<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use LaravelJsonApi\Core\Exceptions\JsonApiException;
use Symfony\Component\HttpFoundation\Response;

class RefreshToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // exclude auth routes
        $exclude = [
            'v1.users.signin',
            'v1.users.signup',
            'v1.users.signout',
        ];

        if ($request->routeIs($exclude)) {
            return $next($request);
        }

        // if user is authenticated, refresh the token
        if ($request->user()) {
            $request->user()->tokens()->update(['expires_at' => now()->addDays(5)]);
            return $next($request);
        }

        // get the bearer token from the request
        $bearerToken = $request->bearerToken();

        // if the bearer token is provided, find the token
        if ($bearerToken) {
            $token = PersonalAccessToken::findToken($bearerToken);

            // if the token is expired, delete it and throw an exception
            if ($token && $token->expires_at->isPast()) {
                $token->delete();

                throw JsonApiException::error(
                    [
                        'status' => 400, // Wrong request
                        'detail' => __('auth.token_expired')
                    ]
                );
            }

            // if the token is not expired, update the expiration date
            if ($token && $token->expires_at->isFuture()) {
                $token->update(['expires_at' => now()->addDays(5)]);
            }
        } else {
            // if the bearer token is not provided, throw an exception
            throw JsonApiException::error(
                [
                    'status' => 401, // Unauthorized
                    'detail' => __('auth.token_not_provided')
                ]
            );
        }

        return $next($request);
    }
}
