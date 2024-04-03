<?php

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
    $server->resource('muscles', JsonApiController::class);

    // Definitions for Workout model
    $server->resource('workouts', JsonApiController::class)
        ->relationships(function (Relationships $relationships) {
            $relationships->hasOne('subcategory');
        });
});
