<?php

use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VariationController;
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
            $relationships->hasMany('workouts');
            $relationships->hasMany('translations');
        });

    // Definitions for Muscle model
    $server->resource('muscles', JsonApiController::class)
        ->relationships(function (Relationships $relationships) {
            $relationships->hasMany('workouts');
            $relationships->hasMany('variations');
            $relationships->hasMany('translations');
        });

    // Definitions for Workout model
    $server->resource('workouts', WorkoutController::class)
        ->relationships(function (Relationships $relationships) {
            $relationships->hasOne('category');
            $relationships->hasMany('muscles');
            $relationships->hasMany('routines');
            $relationships->hasMany('variations');
            $relationships->hasMany('translations');
        });

    // Definitions for Variation model
    $server->resource('variations', VariationController::class)
        ->relationships(function (Relationships $relationships) {
            $relationships->hasOne('workout');
            $relationships->hasMany('muscles');
            $relationships->hasMany('translations');
        });

    // Definitions for Routine model
    $server->resource('routines', JsonApiController::class)
        ->relationships(function (Relationships $relationships) {
            $relationships->hasMany('plans');
            $relationships->hasMany('workouts');
            $relationships->hasMany('translations');
        });

    // Definitions for Frequency model
    $server->resource('frequencies', JsonApiController::class)
        ->relationships(function (Relationships $relationships) {
            $relationships->hasMany('translations');
        });

    // Definitions for Goal model
    $server->resource('goals', JsonApiController::class)
        ->relationships(function (Relationships $relationships) {
            $relationships->hasMany('translations');
        });

    // Definitions for PhysicalCondition model
    $server->resource('physical-conditions', JsonApiController::class)
        ->relationships(function (Relationships $relationships) {
            $relationships->hasMany('translations');
        });

    // Definitions for Plan model
    $server->resource('plans', JsonApiController::class)
        ->relationships(function (Relationships $relationships) {
            $relationships->hasOne('goal');
            $relationships->hasOne('frequency');
            $relationships->hasOne('physicalCondition');
            $relationships->hasMany('users');
            $relationships->hasMany('routines');
            $relationships->hasMany('translations');
        });

    // Definitions for User model
    $server->resource('users', UserController::class)
        ->relationships(function (Relationships $relationships) {
            $relationships->hasMany('plans');
        })
        ->actions('auth', function ($actions) {
            // Login action
            $actions->post('signin');
            // Register action
            $actions->post('signup');
            // Logout action
            $actions->post('signout');
            // Refresh token action
            $actions->post('refresh');
        });

    // Definitions for Translation model
    $server->resource('translations', JsonApiController::class);

    // Definitions for Role model
    $server->resource('roles', RoleController::class)
        ->relationships(function (Relationships $relationships) {
            $relationships->hasMany('permissions');
        })
        ->actions('manage', function ($actions) {
            // Create action
            $actions->post('createRole');
            // Update action
            $actions->patch('updateRole');
            // Delete action
            $actions->delete('deleteRole');
            // Flat list
            $actions->get('flatList');
        });

    // Definitions for Permission model
    $server->resource('permissions', PermissionController::class)
        ->relationships(function (Relationships $relationships) {
            $relationships->hasMany('roles');
        })
        ->actions('manage', function ($actions) {
            // Create action
            $actions->post('createPermission');
            // Update action
            $actions->patch('updatePermission');
            // Delete action
            $actions->delete('deletePermission');
            // Flat list
            $actions->get('flatList');
        });
});
