<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Variation;
use App\JsonApi\V1\Variations\VariationRequest;
use LaravelJsonApi\Laravel\Http\Controllers\Actions;


class VariationController extends Controller
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

    /**
     * Save the image when the variation is saved. This method is called after the
     * variation is saved. If the image is not present in the request, it does nothing.
     *
     * @param Variation $variation
     * @param VariationRequest $request
     *
     * @return void
     */
    public function saved(Variation $variation, VariationRequest $request): void
    {
        if (!isset($request->data['attributes']['image'])) {
            return;
        }

        $variation
            ->addMedia($request->data['attributes']['image'])
            ->toMediaCollection();
    }
}
