<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\TokenService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Schedule token cleanup
Schedule::call(function () {
    $tokenService = app(TokenService::class);
    $deletedCount = $tokenService->cleanupExpiredTokens();

    if ($deletedCount > 0) {
        logger("Cleaned up {$deletedCount} expired tokens");
    }
})->daily()->at('02:00'); // Run daily at 2 AM

// Alternative: Create an Artisan command for token cleanup
Artisan::command('tokens:cleanup', function () {
    $tokenService = app(TokenService::class);
    $deletedCount = $tokenService->cleanupExpiredTokens();

    $this->info("Cleaned up {$deletedCount} expired tokens");
})->purpose('Clean up expired tokens');
