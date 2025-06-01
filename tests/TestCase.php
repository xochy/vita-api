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
            'id' => $model->id,
            'path' => $path ?? $model->getTable(),
            'collection' => $collection,
            'files' => is_array($files) ? $files : [$files],
        ];
    }
}
