<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Validators\MediaFieldsValidator;
use Illuminate\Http\Request;
use LaravelJsonApi\Core\Exceptions\JsonApiException;
use LaravelJsonApi\Laravel\Http\Controllers\Actions;
use Spatie\MediaLibrary\Support\MediaStream;

class MediaController extends Controller
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

    protected $mediaValidator;

    /**
     * Initialize the media valitor for downloading functions.
     *
     * @param MediaFieldsValidator $mediaValidator
     */
    public function __construct(MediaFieldsValidator $mediaValidator)
    {
        $this->mediaValidator = $mediaValidator;
    }

    /**
     * Get the media file from the request.
     *
     * @param Request $request
     *
     * @return Media
     */
    public function downloadFile(Request $request): Media
    {
        return $this->getSingleFile($request);
    }

    /**
     * Get the media files from the request.
     *
     * @param Request $request
     *
     * @return MediaStream|JsonApiException
     */
    public function downloadFiles(Request $request): MediaStream|JsonApiException
    {
        $validator = $this->mediaValidator->validator($request, false);

        if ($validator->stopOnFirstFailure()->fails()) {
            throw JsonApiException::error(
                [
                    'status' => 400, // Wrong request
                    'detail' => $validator->errors()->first()
                ]
            );
        }

        $medias = Media::whereIn('id', $request->data['ids'])->get();

        return MediaStream::create('files.zip')
            ->useZipOptions(function (&$zipOptions) {
                $zipOptions['sendHttpHeaders'] = false;
            })
            ->addMedia($medias);
    }

    /**
     * Output the media file to the browser.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function outputFile(Request $request): mixed
    {
        $media = $this->getSingleFile($request);

        return $media->toResponse($request);
    }

    /**
     * Get the single media file from the request.
     *
     * @param Request $request
     *
     * @return Media|JsonApiException
     */
    private function getSingleFile(Request $request): Media|JsonApiException
    {
        $validator = $this->mediaValidator->validator($request);

        if ($validator->stopOnFirstFailure()->fails()) {
            throw JsonApiException::error(
                [
                    'status' => 400, // Wrong request
                    'detail' => $validator->errors()->first()
                ]
            );
        }

        return Media::find($request->data['id']);
    }
}
