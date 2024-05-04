<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use LaravelJsonApi\Core\Exceptions\JsonApiException;
use App\Exceptions\InvalidOrderException;
use Illuminate\Support\Facades\Log;
use LaravelJsonApi\Exceptions\ExceptionParser;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->appendToGroup('api', App\Http\Middleware\Localization::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
