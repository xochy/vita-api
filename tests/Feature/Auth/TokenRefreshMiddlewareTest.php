<?php

namespace Tests\Feature\Auth;

use App\Http\Middleware\TokenRefreshMiddleware;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;
use LaravelJsonApi\Core\Exceptions\JsonApiException;

class TokenRefreshMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    const WORKS_URI = 'v1/workouts';
    const JSON_API_ERROR = '/JSON:API error/';

    protected User $user;
    protected TokenRefreshMiddleware $middleware;

    public function setUp(): void
    {
        parent::setUp();

        // Create a user for testing purposes
        $this->user = User::factory()->create();
        // Instantiate the middleware
        $this->middleware = new TokenRefreshMiddleware();
    }

    /**
     * Helper to create a request with a specific route and bearer token.
     */
    protected function createRequest(string $uri, array $routeAction, ?string $bearerToken = null): Request
    {
        $request = Request::create($uri, 'GET');

        $request->setRouteResolver(
            function () use ($request, $uri, $routeAction) {
                return (new Route(
                    'GET',
                    $uri,
                    $routeAction
                ))->bind($request);
            }
        );
        if ($bearerToken) {
            $request->headers->set('Authorization', 'Bearer ' . $bearerToken);
        }
        return $request;
    }

    /** @test */
    public function it_allows_excluded_routes_without_token()
    {
        // Test with an excluded route (e.g., signin)
        $request = $this->createRequest(
            'v1/users/auth/signin',
            [
                'as' => 'v1.users.signin'
            ]
        );

        $next = function ($_) {
            return new Response('Success', 200);
        };

        $response = $this->middleware->handle($request, $next);

        // Assert that the middleware allowed the request to proceed
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Success', $response->getContent());

        // Test with another excluded route (e.g., api.health)
        $request = $this->createRequest('v1/health', ['as' => 'api.health']);
        $response = $this->middleware->handle($request, $next);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Success', $response->getContent());
    }

    /** @test */
    public function it_fails_on_missing_token_for_protected_routes()
    {
        // Create a request to a protected route without a token
        $request = $this->createRequest(
            self::WORKS_URI,
            [
                'as' => 'v1.workouts.index'
            ]
        );

        $next = function ($_) {
            return new Response('Success', 200); // This should not be reached
        };

        // Expect a JsonApiException with 'token_not_provided' detail
        $this->expectException(JsonApiException::class);

        $this->expectExceptionMessageMatches(
            self::JSON_API_ERROR
        ); // Using regex to match part of the message

        $this->middleware->handle($request, $next);
    }

    /** @test */
    public function it_fails_on_invalid_token_for_protected_routes()
    {
        // Create a request to a protected route with an invalid token
        $request = $this->createRequest(
            self::WORKS_URI,
            [
                'as' => 'v1.workouts.index'
            ],
            'invalid-token-string'
        );

        $next = function ($_) {
            return new Response('Success', 200); // This should not be reached
        };

        // Expect a JsonApiException with 'invalid_token' detail
        $this->expectException(JsonApiException::class);

        $this->expectExceptionMessageMatches(
            self::JSON_API_ERROR
        );

        $this->middleware->handle($request, $next);
    }

    /** @test */
    public function it_fails_on_expired_token_for_protected_routes()
    {
        // Create a token that has expired
        $token = $this->user->createToken('test_token')->accessToken;
        $token->expires_at = Carbon::now()->subMinutes(5); // Set expiration to 5 minutes ago
        $token->save();

        $this->assertDatabaseHas(
            'personal_access_tokens',
            [
                'id' => $token->id,
                'expires_at' => $token->expires_at, // Ensure it's saved as past
            ]
        );

        $request = $this->createRequest(
            self::WORKS_URI,
            ['as' => 'v1.workouts.index'],
            $token->token
        );

        $next = function ($_) {
            return new Response('Success', 200); // This should not be reached
        };

        // Expect a JsonApiException with 'token_expired' detail
        $this->expectException(JsonApiException::class);

        $this->expectExceptionMessageMatches(
            self::JSON_API_ERROR
        );

        $this->middleware->handle($request, $next);

        // Assert that the expired token has been deleted from the database
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->id,
        ]);
    }

    /** @test */
    public function it_updates_last_used_at_for_valid_token_on_protected_routes()
    {
        // Create a valid token
        $plainTextToken = $this->user->createToken('test_token')->plainTextToken;
        $tokenModel = PersonalAccessToken::where('tokenable_id', $this->user->id)->first();
        $originalLastUsedAt = $tokenModel->last_used_at;

        // Advance time to ensure last_used_at actually changes
        Carbon::setTestNow(Carbon::now()->addMinute());

        $request = $this->createRequest(
            self::WORKS_URI,
            [
                'as' => 'v1.workouts.index'
            ],
            $plainTextToken
        );

        $next = function ($_) {
            return new Response('Success', 200);
        };

        $response = $this->middleware->handle($request, $next);

        // Assert that the request was successful
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Success', $response->getContent());

        // Reload the token from the database
        $updatedToken = PersonalAccessToken::find($tokenModel->id);

        // Assert that 'last_used_at' has been updated
        $this->assertNotNull($updatedToken->last_used_at);

        $this->assertGreaterThan(
            $originalLastUsedAt,
            $updatedToken->last_used_at
        );

        // Assert that 'expires_at' has NOT been updated by the middleware
        // This is based on your middleware comment:
        // "Don't update expires_at here - let Sanctum handle it naturally"
        $this->assertEquals(
            $tokenModel->expires_at,
            $updatedToken->expires_at
        );

        // Reset Carbon's test time
        Carbon::setTestNow(null);
    }
}
