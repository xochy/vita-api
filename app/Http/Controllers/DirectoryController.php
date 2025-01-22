<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\JsonApi\V1\Directories\DirectoryRequest;
use App\Models\Directory;
use App\Validators\DirectoryFieldsValidator;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use LaravelJsonApi\Core\Exceptions\JsonApiException;
use LaravelJsonApi\Core\Responses\DataResponse;
use LaravelJsonApi\Laravel\Http\Controllers\Actions;

class DirectoryController extends Controller
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

    const STORE_ACTION = 'store';
    const UPDATE_ACTION = 'update';
    const DELETE_ACTION = 'delete';

    protected $mediaController;
    protected $directoryValidator;

    /**
     * Initialize the directory validator for the controller.
     *
     * @param MediaController $mediaController
     * @param DirectoryFieldsValidator $directoryValidator
     */
    public function __construct(MediaController $mediaController, DirectoryFieldsValidator $directoryValidator)
    {
        $this->mediaController = $mediaController;
        $this->directoryValidator = $directoryValidator;
    }

    /**
     * Make actions after the directory is saved.
     *
     * @param Directory $directory
     * @param DirectoryRequest $request
     *
     * @return void
     */
    public function saved(Directory $directory, DirectoryRequest $request): void
    {
        $this->saveParentDirectory($directory, $request);
    }

    /**
     * Make actions after the directory is updated.
     *
     * @param Directory $directory
     * @param DirectoryRequest $request
     *
     * @return void
     */
    public function updated(Directory $directory, DirectoryRequest $request): void
    {
        $this->manageMedia($directory, $request);
    }

    /**
     * Upload a file related to the directory.
     *
     * @param Request $request
     *
     * @return void
     */
    public function uploadDirectoryFile(Request $request): DataResponse|JsonApiException
    {
        $directory = Directory::find($request->directoryId);
        return $this->mediaController->uploadFile($request, $directory);
    }

    /**
     * Determine if the directory can manage media.
     *
     * @param Directory $directory
     * @param DirectoryRequest $request
     *
     * @return void
     */
    private function manageMedia(Directory $directory, DirectoryRequest $request)
    {
        if (!isset($request->data['meta']['media'])) {
            return;
        }

        $validator = $this->directoryValidator->generalMediaValidator($request);

        if ($validator->stopOnFirstFailure()->fails()) {
            throw JsonApiException::error(
                [
                    'status' => 400, // Wrong request
                    'detail' => $validator->errors()->first()
                ]
            );
        }

        switch ($request->data['meta']['media']['action']) {
            case self::STORE_ACTION:
                $this->storeMedia($directory, $request);
                break;
            case self::UPDATE_ACTION:
                $this->updateMedia($directory, $request);
                break;
            case self::DELETE_ACTION:
                $this->deleteMedia($directory, $request);
                break;
            default:
                throw JsonApiException::error(
                    [
                        'status' => 400, // Wrong request
                        'detail' => __('directories.media_action_invalid')
                    ]
                );
        }
    }

    /**
     * Add media to the directory. The media can be uploaded files or base64 encoded files.
     *
     * @param Directory $directory
     * @param DirectoryRequest $request
     *
     * @return void
     */
    private function storeMedia(Directory $directory, DirectoryRequest $request)
    {
        foreach ($request->data['meta']['media']['data'] as $media) {
            $path = $request->data['meta']['media']['path'];

            if ($media instanceof UploadedFile) {
                $directory
                    ->addMedia($media)
                    ->withCustomProperties(
                        [
                            'zip_filename_prefix' => $path
                        ]
                    )
                    ->toMediaCollection((string) $directory->id);
                continue;
            }

            $validator = $this->directoryValidator->base64MediaValidator($media);

            if ($validator->stopOnFirstFailure()->fails()) {
                throw JsonApiException::error(
                    [
                        'status' => 400, // Wrong request
                        'detail' => $validator->errors()->first()
                    ]
                );
            }

            $directory
                ->addMediaFromBase64($media['content'])
                ->usingName($media['filename'])
                ->usingFileName($media['filename'])
                ->withCustomProperties(['zip_filename_prefix' => $path])
                ->toMediaCollection($directory->id);
        }
    }

    /**
     * Update the media in the directory. Only the name of the media can be updated.
     *
     * @param Directory $directory
     * @param DirectoryRequest $request
     *
     * @return void
     */
    private function updateMedia(Directory $directory, DirectoryRequest $request)
    {
        foreach ($request->data['meta']['media']['data'] as $mediaPayload) {
            $validator = $this->directoryValidator->updateMediaValidator($mediaPayload);

            if ($validator->stopOnFirstFailure()->fails()) {
                throw JsonApiException::error(
                    [
                        'status' => 400, // Wrong request
                        'detail' => $validator->errors()->first()
                    ]
                );
            }

            $media = $directory->getMedia($directory->id)->find($mediaPayload['id']);
            $media->update(
                [
                    'name' => $mediaPayload['attributes']['filename'],
                    'file_name' => $mediaPayload['attributes']['filename']
                ]
            );
        }
    }

    /**
     * Delete the media from the directory.
     *
     * @param Directory $directory
     * @param DirectoryRequest $request
     *
     * @return void
     */
    private function deleteMedia(Directory $directory, DirectoryRequest $request)
    {
        foreach ($request->data['meta']['media']['data'] as $mediaPayload) {
            $validator = $this->directoryValidator->deleteMediaValidator($mediaPayload);

            if ($validator->stopOnFirstFailure()->fails()) {
                throw JsonApiException::error(
                    [
                        'status' => 400, // Wrong request
                        'detail' => $validator->errors()->first()
                    ]
                );
            }

            $media = $directory->getMedia($directory->id)->find($mediaPayload['id']);
            $media->delete();
        }
    }

    /**
     * Save the parent directory when the directory is saved. This method is called after the
     * directory is saved. If the parent is not present in the request, it does nothing.
     *
     * @param Directory $directory
     * @param DirectoryRequest $request
     *
     * @return void
     */
    private function saveParentDirectory(Directory $directory, DirectoryRequest $request)
    {
        if (!isset($request->data['relationships']['parent'])) {
            return;
        }

        $parentId = $request->data['relationships']['parent']['data']['id'];

        if (!DB::table('directories')->where('id', $parentId)->exists()) {
            throw JsonApiException::error(
                [
                    'status' => 400, // Wrong request
                    'detail' => __('directories.parent_id_invalid')
                ]
            );
        }

        $directory->parent_id = $parentId;
        $directory->save();
    }
}
