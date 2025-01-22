<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Muscle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use LaravelJsonApi\Core\Exceptions\JsonApiException;
use LaravelJsonApi\Core\Responses\DataResponse;
use LaravelJsonApi\Laravel\Http\Controllers\Actions;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MuscleController extends Controller
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

    protected $mediaController;

    /**
     * Initialize the directory validator for the controller.
     *
     * @param MediaController $mediaController
     */
    public function __construct(MediaController $mediaController)
    {
        $this->mediaController = $mediaController;
    }

    /**
     * Upload files to the media library.
     *
     * @param Request $request
     *
     * @return DataResponse|JsonApiException
     */
    public function uploadFiles(Request $request): DataResponse|JsonApiException
    {
        $muscle = Muscle::find($request->id);
        return $this->mediaController->uploadFiles($request, $muscle);
    }

    /**
     * Download a file from the media library.
     *
     * @param Request $request
     *
     * @return BinaryFileResponse|JsonApiException
     */
    public function downloadFile(Request $request): BinaryFileResponse|JsonApiException
    {
        $muscle = Muscle::find($request->id);
        Log::info('MuscleController::downloadFile', ['muscle' => $muscle]);
        return $this->mediaController->downloadMedia($muscle, $request->mediaId);
    }
}
