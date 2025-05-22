<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Traits\HandlesMedia;
use Illuminate\Http\Request;
use LaravelJsonApi\Core\Exceptions\JsonApiException;
use LaravelJsonApi\Core\Responses\DataResponse;
use LaravelJsonApi\Laravel\Http\Controllers\Actions;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PlanController extends Controller
{
    use Actions\FetchMany;
    use Actions\FetchOne;
    use Actions\Store;
    use Actions\Update;
    use Actions\Destroy;
    use Actions\FetchRelated;
    use Actions\FetchRelationship;
    use Actions\UpdateRelationship;
    use Actions\AttachRelationship;
    use Actions\DetachRelationship;

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
     * Upload media files.
     *
     * @param Request $request
     *
     * @return DataResponse|JsonApiException
     */
    public function uploadFiles(Request $request): DataResponse|JsonApiException
    {
        $model = Plan::find($request->id);
        if (!$model) {
            throw JsonApiException::error(
                [
                    'status' => 400, // Wrong request
                    'detail' => 'Model not found.',
                ]
            );
        }

        return $this->uploadMediaFiles($request, $model);
    }

    /**
     * Download a media file.
     *
     * @param Request $request
     *
     * @return BinaryFileResponse|JsonApiException
     */
    public function downloadFile(Request $request): BinaryFileResponse|JsonApiException
    {
        $model = Plan::find($request->id);
        if (!$model) {
            throw JsonApiException::error(
                [
                    'status' => 400, // Wrong request
                    'detail' => 'Model not found.',
                ]
            );
        }

        return $this->downloadMediaFile($request, $model);
    }
}
