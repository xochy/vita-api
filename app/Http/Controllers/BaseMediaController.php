<?php

namespace App\Http\Controllers;

use App\Traits\HandlesMedia;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use LaravelJsonApi\Core\Exceptions\JsonApiException;
use LaravelJsonApi\Core\Responses\DataResponse;

abstract class BaseMediaController extends Controller
{
    use HandlesMedia;

    /**
     * Initialize the media controller.
     *
     * @param MediaController $mediaController
     */
    public function __construct(MediaController $mediaController)
    {
        $this->setMediaController($mediaController);
    }

    /**
     * Get the model class for this controller.
     */
    abstract protected function getModelClass(): string;

    /**
     * Upload media files.
     */
    public function uploadFiles(Request $request): DataResponse|JsonApiException
    {
        $model = $this->findModel($request->id);
        return $this->uploadMediaFiles($request, $model);
    }

    /**
     * Download a media file.
     */
    public function downloadFile(Request $request): BinaryFileResponse|JsonApiException
    {
        $model = $this->findModel($request->id);
        return $this->downloadMediaFile($request, $model);
    }

    /**
     * Find a model by ID or throw an exception.
     */
    protected function findModel($id): Model
    {
        $modelClass = $this->getModelClass();
        $model = $modelClass::find($id);

        if (!$model) {
            throw JsonApiException::error([
                'status' => 404,
                'detail' => __('exceptions.model_not_found'),
            ]);
        }

        return $model;
    }
}
