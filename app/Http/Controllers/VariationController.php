<?php

namespace App\Http\Controllers;

use App\Models\Variation;
use LaravelJsonApi\Laravel\Http\Controllers\Actions;


class VariationController extends BaseMediaController
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

    protected function getModelClass(): string
    {
        return Variation::class;
    }
}
