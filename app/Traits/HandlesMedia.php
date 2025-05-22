<?php

namespace App\Traits;

use App\Http\Controllers\MediaController;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use LaravelJsonApi\Core\Exceptions\JsonApiException;
use LaravelJsonApi\Core\Responses\DataResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

trait HandlesMedia
{
    protected MediaController $mediaController;

    /**
     * Set the media controller.
     *
     * @param MediaController $mediaController
     */
    public function setMediaController(MediaController $mediaController): void
    {
        $this->mediaController = $mediaController;
    }

    /**
     * Upload media files.
     *
     * @param Request $request
     * @param Model|Collection $model
     *
     * @return DataResponse|JsonApiException
     */
    public function uploadMediaFiles(Request $request, Model|Collection $model): DataResponse|JsonApiException
    {
        return $this->mediaController->uploadFiles($request, $model);
    }

    /**
     * Download a media file.
     *
     * @param Request $request
     * @param Model|Collection $model
     *
     * @return BinaryFileResponse|JsonApiException
     */
    public function downloadMediaFile(Request $request, Model|Collection $model): BinaryFileResponse|JsonApiException
    {
        return $this->mediaController->downloadMedia($model, $request->mediaId);
    }
}
