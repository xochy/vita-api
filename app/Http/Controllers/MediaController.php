<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Validators\MediaFieldsValidator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use LaravelJsonApi\Core\Exceptions\JsonApiException;
use LaravelJsonApi\Core\Responses\DataResponse;
use LaravelJsonApi\Laravel\Http\Controllers\Actions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\Support\MediaStream;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

    use InteractsWithMedia;

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
     * Upload a file to the media library.
     *
     * @param Request $request
     * @param Model $model
     *
     * @return DataResponse|JsonApiException
     */
    public function uploadFile(Request $request, Model $model): DataResponse|JsonApiException
    {
        if (!$model instanceof HasMedia) {
            throw new JsonApiException(
                [
                    'status' => 400,
                    'detail' => 'The provided model does not implement the HasMedia interface.'
                ]
            );
        }

        try {
            $path = $request->path;
            $media = $model->addMediaFromRequest('file')
                ->withCustomProperties(['zip_filename_prefix' => $path])
                ->toMediaCollection('files');

            return DataResponse::make($media);
        } catch (\Throwable $th) {
            throw JsonApiException::error(
                [
                    'status' => 400, // Wrong request
                    'detail' => $th->getMessage()
                ]
            );
        }
    }

    /**
     * Upload files to the media library.
     *
     * @param Request $request
     * @param Model $model
     *
     * @return DataResponse|JsonApiException
     */
    public function uploadFiles(Request $request, Model|Collection $model): DataResponse|JsonApiException
    {
        if (!$model instanceof HasMedia) {
            throw new JsonApiException(
                [
                    'status' => 400,
                    'detail' => 'The provided model does not implement the HasMedia interface.'
                ]
            );
        }

        try {
            $model->clearMediaCollection($request->collection);

            $path = $request->path;

            $mediaAdders = $model->addMultipleMediaFromRequest(['files']);
            foreach ($mediaAdders as $fileAdder) {
                $fileAdder->withCustomProperties(['zip_filename_prefix' => $path])
                    ->toMediaCollection($request->collection);
            }

            Log::info('Actual files in model collection: ' . $model->getMedia($request->collection));

            return DataResponse::make($model->getMedia($request->collection));
        } catch (\Throwable $th) {
            throw JsonApiException::error(
                [
                    'status' => 400, // Wrong request
                    'detail' => $th->getMessage()
                ]
            );
        }
    }

    public function downloadMedia(Model|Collection $model, $mediaUuid): BinaryFileResponse|JsonApiException
    {
        $media = $model->getMedia('images')->firstWhere('uuid', $mediaUuid);

        if (!$media) {
            throw new JsonApiException(
                [
                    'status' => 404,
                    'detail' => 'Media not found.'
                ]
            );
        }

        try {
            return response()->file($media->getPath(), [
                'Content-Type' => $media->mime_type,
                'Content-Disposition' => 'inline',
            ]);
        } catch (\Throwable $th) {
            throw JsonApiException::error(
                [
                    'status' => 400, // Wrong request
                    'detail' => $th->getMessage()
                ]
            );
        }
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

        try {
            return Media::find($request->data['id'])->first();
        } catch (\Throwable $th) {
            throw JsonApiException::error(
                [
                    'status' => 400, // Wrong request
                    'detail' => $th->getMessage()
                ]
            );
        }
    }
}
