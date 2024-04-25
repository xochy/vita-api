<?php

use App\Http\Controllers\WorkoutController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use LaravelJsonApi\Laravel\Facades\JsonApiRoute;
use LaravelJsonApi\Laravel\Http\Controllers\JsonApiController;
use LaravelJsonApi\Laravel\Routing\Relationships;
use LaravelJsonApi\Laravel\Routing\ResourceRegistrar;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

JsonApiRoute::server('v1')->prefix('v1')->resources(function (ResourceRegistrar $server) {
    // Definitions for Category model
    $server->resource('categories', JsonApiController::class)
        ->relationships(function (Relationships $relationships) {
            $relationships->hasMany('subcategories');
        });

    // Definitions for Subcategory model
    $server->resource('subcategories', JsonApiController::class)
        ->relationships(function (Relationships $relationships) {
            $relationships->hasOne('category');
        });

    // Definitions for Muscle model
    $server->resource('muscles', JsonApiController::class)
        ->relationships(function (Relationships $relationships) {
            $relationships->hasMany('workouts');
        });

    // Definitions for Workout model
    $server->resource('workouts', WorkoutController::class)
        ->relationships(function (Relationships $relationships) {
            $relationships->hasOne('subcategory');
            $relationships->hasMany('muscles');
            $relationships->hasMany('routines');
        });

    // Definitions for Routine model
    $server->resource('routines', JsonApiController::class)
        ->relationships(function (Relationships $relationships) {
            $relationships->hasMany('workouts');
        });

    // Definitions for Frequency model
    $server->resource('frequencies', JsonApiController::class);

    // Definitions for Goal model
    $server->resource('goals', JsonApiController::class);

    // Definitions for PhysicalCondition model
    $server->resource('physical-conditions', JsonApiController::class);

    // Definitions for Plan model
    $server->resource('plans', JsonApiController::class)
        ->relationships(function (Relationships $relationships) {
            $relationships->hasOne('goal');
            $relationships->hasOne('frequency');
            $relationships->hasOne('physicalCondition');
        });
});
