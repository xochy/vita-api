<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use LaravelJsonApi\Testing\MakesJsonApiRequests;

abstract class TestCase extends BaseTestCase
{
    use MakesJsonApiRequests;

    /**
     * Generate form data for media upload tests.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array|\Illuminate\Http\UploadedFile $files
     * @param string $path
     * @param string $collection
     * @return array
     */
    protected function mediaUploadFormData($model, $files, $path = null, $collection = 'images'): array
    {
        return [
            'id'         => $model->id,
            'path'       => $path ?? $model->getTable(),
            'collection' => $collection,
            'files'      => is_array($files) ? $files : [$files],
        ];
    }

    /**
 * Create an user with a personal access token.
 *
 * @param string $role
 * @param string $tokenName
 * @param string $measurement
 * @return array
 */
protected function createUserWithToken(string $role = 'admin', string $tokenName = 'test_token', string $measurement = 'metric')
{
    $factory = \App\Models\User::factory();

    // Apply measurement system based on parameter
    if ($measurement === 'metric') {
        $factory = $factory->metric();
    } elseif ($measurement === 'imperial') {
        $factory = $factory->imperial();
    }
    // If neither 'metric' nor 'imperial', use default random system

    $user = $factory->create()->assignRole($role);
    $bearerToken = "Bearer {$user->createToken($tokenName)->plainTextToken}";
    return [$user, $bearerToken];
}
}
