<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Packages for microservices

Laravel JSON:API [Docs](https://laraveljsonapi.io/docs/3.0/getting-started/).

### JSON:API settings

#### To start, we'll need to install the Laravel JSON:API package into our application, via Composer. Run the following commands:

```javascript
composer require laravel-json-api/laravel
composer require --dev laravel-json-api/testing
php artisan install:api
```

1. Creating models.

```javascript
php artisan make:model Post -a
```

2. Define fields in migration file and fillable values to the model. After this, make sure to specify the defined values in factory file and seeder configuration.

3. Run the following command to create the server.

```javascript
php artisan jsonapi:server v1
```
- This creates a new file in your application: `app/JsonApi/V1/Server.php`
- It's worth noting at this point that the $baseUri property is set to /api/v1. This means all the HTTP requests we send to our API will start with http://localhost/api/v1/.

4. There's one thing we need to do at this point: we need to tell Laravel JSON:API that we have a v1 server. To do that, we need to edit our `config/jsonapi.php`. so that this configuration looks like this:

```php
'servers' => [
-//  'v1' => \App\JsonApi\V1\Server::class,
+    'v1' => \App\JsonApi\V1\Server::class,
 ],
```

#### Creating the Schema

5. To create the schema, run the following command.

```javascript
php artisan jsonapi:schema posts
```
- This creates a new file, `app/JsonApi/V1/Posts/PostSchema.php`.
- Our new PostSchema class defines the posts resource, which is the JSON:API representation of the Post model - notice how that is defined on the the static $model property of the class.

6. Tell our JSON:API server that the schema exists. To do this, we update the allSchemas() method in our `app/JsonApi/Server.php` file. Update that to look like this:

```php
 protected function allSchemas(): array
 {
     return [
-        // @TODO
+        Posts\PostSchema::class,
     ];
 }
```

#### Disabling Authorization

- If you do not want a specific JSON:API resource to be authorized, then you can override the authorizable method on the JSON:API schema.

```php
namespace App\JsonApi\V1\Posts;

use LaravelJsonApi\Eloquent\Schema;

class PostSchema extends Schema
{

    // ...

    /**
     * Determine if the resource is authorizable.
     *
     * @return bool
     */
    public function authorizable(): bool
    {
        return false;
    }
}
```

- If you do not want our authorization logic to run for an entire server, then you can override the authorizable method on the server.

```php
namespace App\JsonApi\V1;

use LaravelJsonApi\Core\Server\Server as BaseServer;

class Server extends BaseServer
{
    // ...

    /**
     * Determine if the server is authorizable.
     *
     * @return bool
     */
    public function authorizable(): bool
    {
        return false;
    }
}
```

#### Schema fields

The fields() method on the schema defines the attributes and relationships that our resource has. Notice the created file has a few standard fields in it already: the ID field for the resource, and the createdAt and updatedAt dates that are standard on an Eloquent model.

7. Add the fields to the schema fields function.

```php
public function fields(): array
{
    return [
        ID::make(),
        Str::make('content'),
        DateTime::make('createdAt')->sortable()->readOnly(),
+       DateTime::make('publishedAt')->sortable(),
+       Str::make('slug'),
+       Str::make('title')->sortable(),
        DateTime::make('updatedAt')->sortable()->readOnly(),
    ];
}
```

#### Routing

After continue, make sure to add <code>api: __DIR__ . '/../routes/api.php',</code> to the <code>bootstrap/app.php</code> file in <code>withRouting</code> method.

8. To add our JSON:API server's routes, we will use the JsonApiRoute facade. Update the `routes/api.php` file to look like this.

```php
 use Illuminate\Http\Request;
 use Illuminate\Support\Facades\Route;
+use LaravelJsonApi\Laravel\Facades\JsonApiRoute;
+use LaravelJsonApi\Laravel\Http\Controllers\JsonApiController;
+use LaravelJsonApi\Laravel\Routing\ResourceRegistrar;

 Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
     return $request->user();
 });
+
+JsonApiRoute::server('v1')->prefix('v1')->resources(function (ResourceRegistrar $server) {
+    $server->resource('posts', JsonApiController::class)->readOnly();
+});
```

#### Validation

9. When receiving the request to create a resource, one thing our server will need to do is validate the JSON that the client has sent. Generate the request class by running the following command.

```javascript
php artisan jsonapi:request posts
```

#### Authentication (optional)

10. Laravel JSON:API uses Laravel's policy implementation to authorise requests to the API. This means for our posts resource we need to create a PostPolicy. You can do this using the following Laravel command.

```javascript
php artisan make:policy PostPolicy --model Post
```

# Laravel Sanctum Token Management Guide

A comprehensive guide for properly managing user tokens in Laravel with Sanctum, including best practices for token refresh, expiration handling, and cleanup.

## Table of Contents

- [Overview](#overview)
- [Common Issues](#common-issues)
- [Installation & Configuration](#installation--configuration)
- [Core Components](#core-components)
- [Implementation](#implementation)
- [Laravel 11 Scheduling](#laravel-11-scheduling)
- [Frontend Integration](#frontend-integration)
- [Best Practices](#best-practices)
- [Troubleshooting](#troubleshooting)

## Overview

This guide addresses common token management issues in Laravel Sanctum applications, particularly:

- 401 Unauthorized errors after token refresh
- `last_used_at` and `expires_at` fields having identical timestamps
- Proper token lifecycle management
- Automated cleanup of expired tokens

## Common Issues

### Problem: Type Error with addDays()
```
TypeError: Carbon\Carbon::rawAddUnit(): Argument #3 ($value) must be of type int|float, string given
```

**Solution**: Environment variables are always strings. Cast config values to integers:
```php
$expirationDays = (int) config('sanctum.expiration_days', 7);
```

### Problem: Conflicting Token Updates
- Multiple places updating token expiration
- `last_used_at` and `expires_at` always identical
- Race conditions in middleware

**Solution**: Separate concerns - track activity vs. extending lifetime.

## Installation & Configuration

### 1. Environment Variables

Add these to your `.env` file:

```env
SANCTUM_TOKEN_EXPIRATION_DAYS=7
SANCTUM_TOKEN_EXPIRATION=null
```

### 2. Sanctum Configuration

Update `config/sanctum.php`:

```php
<?php

return [
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
        env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
    ))),

    'guard' => ['web'],

    // Set to null to use expires_at field
    'expiration' => env('SANCTUM_TOKEN_EXPIRATION', null),

    // Custom configuration for token expiration in days
    'expiration_days' => (int) env('SANCTUM_TOKEN_EXPIRATION_DAYS', 7),

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    'middleware' => [
        'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
        'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
    ],
];
```

## Core Components

### 1. Token Service

Create `app/Services/TokenService.php`:

```php
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
        // Revoke existing tokens for the same device
        $user->tokens()->where('name', $deviceName)->delete();

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
        
        $token->update([
            'expires_at' => now()->addDays($expirationDays),
            'last_used_at' => now()
        ]);

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
     * Clean up expired tokens
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
```

### 2. Improved Token Response

Update `app/Http/Responses/TokenResponse.php`:

```php
<?php

namespace App\Http\Responses;

use App\Models\User;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TokenResponse implements Responsable
{
    private User $user;
    private ?string $token;

    public function __construct(User $user, string $token = null)
    {
        $this->user = $user;
        $this->token = $token;
    }

    public function toResponse($request): JsonResponse
    {
        $permissions = $this->user->getAllPermissions()->pluck('name')->toArray();

        // Create new token or refresh existing one
        if ($this->token) {
            // Refresh existing token
            $expirationDays = (int) config('sanctum.expiration_days', 7);
            $tokenModel = $this->user->tokens()
                ->where('token', hash('sha256', $this->token))
                ->first();
                
            if ($tokenModel) {
                $tokenModel->update([
                    'expires_at' => now()->addDays($expirationDays),
                    'last_used_at' => now()
                ]);
            }
        } else {
            // Create new token
            $deviceName = $request->input('data.attributes.device_name', 'Unknown Device');
            $expirationDays = (int) config('sanctum.expiration_days', 7);
            
            $this->token = $this->user->createToken(
                $deviceName,
                $permissions,
                now()->addDays($expirationDays)
            )->plainTextToken;
        }

        $permissionsPayload = encryptPayload(json_encode($permissions));

        return response()->json([
            'status' => 200,
            'token' => $this->token,
            'name' => $this->user->name,
            'email' => $this->user->email,
            'permissions' => $permissionsPayload,
            'expires_at' => now()->addDays((int) config('sanctum.expiration_days', 7))->toISOString(),
        ]);
    }
}
```

### 3. Token Refresh Middleware

Create `app/Http/Middleware/TokenRefreshMiddleware.php`:

```php
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
        $token->update(['last_used_at' => now()]);

        return $next($request);
    }
}
```

## Implementation

### 1. Update Your Login Controller

```php
use App\Services\TokenService;

public function login(Request $request, TokenService $tokenService)
{
    // ... authentication logic ...
    
    $tokenData = $tokenService->createToken(
        $user, 
        $request->input('device_name', 'Unknown Device'),
        $user->getAllPermissions()->pluck('name')->toArray()
    );
    
    return new TokenResponse($user, $tokenData->plainTextToken);
}
```

### 2. Register the Middleware

In `bootstrap/app.php` (Laravel 11):

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'token.refresh' => \App\Http\Middleware\TokenRefreshMiddleware::class,
    ]);
})
```

Then apply it to your routes:

```php
Route::middleware(['token.refresh'])->group(function () {
    // Your protected routes
});
```

## Laravel 11 Scheduling

Laravel 11 doesn't use `app/Console/Kernel.php`. Here are the updated approaches:

### Option 1: Using `routes/console.php` (Recommended)

```php
<?php

use Illuminate\Support\Facades\Schedule;
use App\Services\TokenService;

// Schedule token cleanup
Schedule::call(function () {
    $tokenService = app(TokenService::class);
    $deletedCount = $tokenService->cleanupExpiredTokens();
    
    if ($deletedCount > 0) {
        logger("Cleaned up {$deletedCount} expired tokens");
    }
})->daily()->at('02:00'); // Run daily at 2 AM

// Create an Artisan command for manual cleanup
Artisan::command('tokens:cleanup', function () {
    $tokenService = app(TokenService::class);
    $deletedCount = $tokenService->cleanupExpiredTokens();
    
    $this->info("Cleaned up {$deletedCount} expired tokens");
})->purpose('Clean up expired tokens');
```

### Option 2: Dedicated Artisan Command

Create `app/Console/Commands/CleanupExpiredTokens.php`:

```php
<?php

namespace App\Console\Commands;

use App\Services\TokenService;
use Illuminate\Console\Command;

class CleanupExpiredTokens extends Command
{
    protected $signature = 'tokens:cleanup {--dry-run : Show what would be deleted without actually deleting}';
    protected $description = 'Clean up expired personal access tokens';

    public function handle(TokenService $tokenService): int
    {
        $this->info('Starting token cleanup...');

        if ($this->option('dry-run')) {
            $count = $tokenService->countExpiredTokens();
            $this->info("Would delete {$count} expired tokens (dry run)");
            return Command::SUCCESS;
        }

        $deletedCount = $tokenService->cleanupExpiredTokens();
        
        if ($deletedCount > 0) {
            $this->info("Successfully cleaned up {$deletedCount} expired tokens");
        } else {
            $this->info('No expired tokens found');
        }

        return Command::SUCCESS;
    }
}
```

Then schedule it in `routes/console.php`:

```php
Schedule::command('tokens:cleanup')->daily()->at('02:00');
```

### Option 3: Bootstrap Configuration

In `bootstrap/app.php`:

```php
->withSchedule(function (Schedule $schedule) {
    $schedule->command('tokens:cleanup')
             ->daily()
             ->at('02:00')
             ->withoutOverlapping()
             ->runInBackground();
})
```

## Frontend Integration

### JavaScript/TypeScript Example

```javascript
// Token management utility
class TokenManager {
    constructor() {
        this.token = localStorage.getItem('auth_token');
        this.setupAxiosInterceptors();
    }

    setupAxiosInterceptors() {
        // Request interceptor to add token
        axios.interceptors.request.use(config => {
            if (this.token) {
                config.headers.Authorization = `Bearer ${this.token}`;
            }
            return config;
        });

        // Response interceptor to handle token expiration
        axios.interceptors.response.use(
            response => response,
            error => {
                if (error.response?.status === 401) {
                    this.handleTokenExpiration();
                }
                return Promise.reject(error);
            }
        );
    }

    setToken(token) {
        this.token = token;
        localStorage.setItem('auth_token', token);
    }

    removeToken() {
        this.token = null;
        localStorage.removeItem('auth_token');
    }

    handleTokenExpiration() {
        this.removeToken();
        // Redirect to login or show login modal
        window.location.href = '/login';
    }
}

// Initialize token manager
const tokenManager = new TokenManager();
```

### React Hook Example

```jsx
import { useEffect, useState } from 'react';
import axios from 'axios';

export const useAuth = () => {
    const [token, setToken] = useState(localStorage.getItem('auth_token'));
    const [user, setUser] = useState(null);

    useEffect(() => {
        if (token) {
            axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        } else {
            delete axios.defaults.headers.common['Authorization'];
        }
    }, [token]);

    const login = async (credentials) => {
        try {
            const response = await axios.post('/api/v1/users/signin', credentials);
            const { token: newToken, name, email } = response.data;
            
            setToken(newToken);
            setUser({ name, email });
            localStorage.setItem('auth_token', newToken);
            
            return { success: true };
        } catch (error) {
            return { success: false, error: error.response?.data };
        }
    };

    const logout = async () => {
        try {
            await axios.post('/api/v1/users/signout');
        } catch (error) {
            console.error('Logout error:', error);
        } finally {
            setToken(null);
            setUser(null);
            localStorage.removeItem('auth_token');
        }
    };

    return { token, user, login, logout };
};
```

## Best Practices

### 1. Token Lifecycle Management

- **Creation**: Generate tokens with appropriate expiration times
- **Refresh**: Update `last_used_at` on each request, extend `expires_at` only when needed
- **Cleanup**: Regularly remove expired tokens to prevent database bloat
- **Revocation**: Provide mechanisms to revoke tokens (logout, security incidents)

### 2. Security Considerations

- Use HTTPS in production
- Implement proper CORS settings
- Set reasonable token expiration times (7-30 days)
- Consider implementing token rotation for high-security applications
- Monitor for suspicious token usage patterns

### 3. Performance Optimization

- Index the `expires_at` column for faster cleanup queries
- Use background jobs for bulk token operations
- Implement token caching for frequently accessed tokens
- Consider using Redis for session-like token storage

### 4. Monitoring and Logging

```php
// Add to your TokenService methods
Log::info('Token created', [
    'user_id' => $user->id,
    'device_name' => $deviceName,
    'expires_at' => $expiresAt
]);

Log::warning('Token expired and deleted', [
    'token_id' => $token->id,
    'user_id' => $token->tokenable_id
]);
```

## Manual Commands

### Running Token Cleanup

```bash
# Manual cleanup
php artisan tokens:cleanup

# Dry run to see what would be deleted
php artisan tokens:cleanup --dry-run

# Run all scheduled tasks
php artisan schedule:run

# List all scheduled tasks
php artisan schedule:list
```

### Production Cron Setup

Add to your server's crontab:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Troubleshooting

### Common Issues and Solutions

1. **TypeError with addDays()**
   - **Problem**: Environment variables are strings
   - **Solution**: Cast to integer: `(int) config('sanctum.expiration_days', 7)`

2. **401 Errors After Token Refresh**
   - **Problem**: Token being deleted or not properly updated
   - **Solution**: Check middleware logic and token validation

3. **Database Bloat**
   - **Problem**: Expired tokens not being cleaned up
   - **Solution**: Implement scheduled cleanup job

4. **Frontend Token Expiration**
   - **Problem**: No handling of expired tokens
   - **Solution**: Implement axios interceptors or similar

### Debug Commands

```bash
# Check token status
php artisan tinker
>>> $token = Laravel\Sanctum\PersonalAccessToken::findToken('your-token-here');
>>> $token->expires_at;
>>> $token->last_used_at;

# Count expired tokens
>>> Laravel\Sanctum\PersonalAccessToken::where('expires_at', '<', now())->count();
```

### Environment Variables Checklist

```env
# Required
SANCTUM_TOKEN_EXPIRATION_DAYS=7
SANCTUM_TOKEN_EXPIRATION=null

# Optional
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000,127.0.0.1,127.0.0.1:8000
SANCTUM_TOKEN_PREFIX=
```

## Conclusion

This guide provides a comprehensive solution for managing Laravel Sanctum tokens effectively. The key principles are:

1. **Separate Concerns**: Activity tracking vs. token lifetime extension
2. **Type Safety**: Always cast config values to appropriate types
3. **Proper Cleanup**: Regular removal of expired tokens
4. **Frontend Integration**: Handle token expiration gracefully
5. **Monitoring**: Log important token lifecycle events

By following these practices, you'll have a robust token management system that handles edge cases and provides a smooth user experience.

---

## Testing

If the purpose is using TDD for develop, is recommended to make this configuration.

1. Publish stubs.

```javascript
php artisan stub:publish
```

2. Add this code to the test.stub file.

```php
<?php

namespace {{ namespace }};

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class {{ class }} extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        if (!Role::whereName('admin')->exists()) {
            $this->seed(RoleSeeder::class);
            // TODO: add seed permissions model class here
        }

        $this->user = User::factory()->create()->assignRole('admin');
    }

    #[Test]
    public function it_can_test()
    {
        //
    }
}
```

3. Edit `TestCase.php` file adding MakesJsonApiRequests trait.

```php
<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use LaravelJsonApi\Testing\MakesJsonApiRequests;

abstract class TestCase extends BaseTestCase
{
    use MakesJsonApiRequests;

    // More functions if needed
}
```

# Equipments and Workouts Relationships Guide

## Problem: BadMethodCallException with hasAttached()

When using Laravel factories with many-to-many relationships, you might encounter this error:

```
BadMethodCallException: Call to undefined method App\Models\Workout::equipment()
```

This occurs when the factory method `hasAttached()` doesn't match your model's relationship method name.

## Root Cause

If your model has a relationship method named `equipments()` (plural):

```php
public function equipments(): BelongsToMany
{
    return $this->belongsToMany(Equipment::class, 'equipment_workout');
}
```

But you're calling:

```php
$workout = Workout::factory()
    ->hasAttached(Equipment::factory()->count(2))
    ->create();
```

Laravel tries to find a method called `equipment()` (singular) which doesn't exist.

## Solutions

### Solution 1: Use `has()` method (Recommended)

```php
$workout = Workout::factory()
    ->forCategory()
    ->has(Equipment::factory()->count(2), 'equipments')
    ->create();
```

### Solution 2: Use `hasAttached()` with explicit relationship name

```php
$workout = Workout::factory()
    ->forCategory()
    ->hasAttached(
        Equipment::factory()->count(2),
        'equipments'
    )
    ->create();
```

### Solution 3: Add factory methods to WorkoutFactory

```php
// In your WorkoutFactory.php
public function withEquipments(int $count = 2): static
{
    return $this->hasAttached(
        Equipment::factory()->count($count),
        'equipments'
    );
}

public function withSpecificEquipments(array $equipmentIds): static
{
    return $this->hasAttached(
        Equipment::whereIn('id', $equipmentIds)->get(),
        'equipments'
    );
}

// Usage:
$workout = Workout::factory()
    ->forCategory()
    ->withEquipments(3)
    ->create();
```

### Solution 4: Use afterCreating callback

```php
// In WorkoutFactory.php
public function withRandomEquipments(int $count = 2): static
{
    return $this->afterCreating(function (Workout $workout) use ($count) {
        $equipments = Equipment::factory()->count($count)->create();
        $workout->equipments()->attach($equipments);
    });
}

// Usage:
$workout = Workout::factory()
    ->forCategory()
    ->withRandomEquipments(3)
    ->create();
```

## Complete Working Examples

### ✅ These work:

```php
// Method 1: Using has() with relationship name
$workout = Workout::factory()
    ->forCategory()
    ->has(Equipment::factory()->count(2), 'equipments')
    ->create();

// Method 2: Using hasAttached with explicit relationship name
$workout = Workout::factory()
    ->forCategory()
    ->hasAttached(
        Equipment::factory()->count(2),
        'equipments'
    )
    ->create();

// Method 3: Create first, then attach
$workout = Workout::factory()->forCategory()->create();
$equipments = Equipment::factory()->count(2)->create();
$workout->equipments()->attach($equipments);

// Method 4: Using existing equipment
$existingEquipments = Equipment::limit(2)->get();
$workout = Workout::factory()
    ->forCategory()
    ->hasAttached($existingEquipments, 'equipments')
    ->create();

// Method 5: With pivot data (if needed)
$workout = Workout::factory()
    ->forCategory()
    ->hasAttached(
        Equipment::factory()->count(2),
        'equipments',
        ['created_at' => now(), 'updated_at' => now()]
    )
    ->create();
```

### ❌ These don't work:

```php
// Missing relationship name
$workout->hasAttached(Equipment::factory()->count(2))

// Method doesn't exist (it's equipments, not equipment)
$workout->equipment()
```

## Key Takeaways

1. **Relationship method names matter**: Laravel factories need to know the exact relationship method name
2. **Use explicit relationship names**: Always specify the relationship name when it's not obvious
3. **Prefer `has()` over `hasAttached()`**: It's more readable and follows Laravel conventions
4. **Create factory states**: For complex relationships, create dedicated factory methods
5. **Test your factories**: Always test factory relationships to catch naming issues early

## Best Practices

1. **Consistent naming**: Use consistent plural/singular naming for relationships
2. **Factory states**: Create specific factory states for different relationship scenarios
3. **Documentation**: Document complex factory relationships for team members
4. **Testing**: Include factory relationship tests in your test suite

## Related Laravel Documentation

- [Database Testing](https://laravel.com/docs/database-testing#factory-relationships)
- [Eloquent Relationships](https://laravel.com/docs/eloquent-relationships#many-to-many)
- [Model Factories](https://laravel.com/docs/database-testing#defining-model-factories)
