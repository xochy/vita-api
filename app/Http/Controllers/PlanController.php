<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use LaravelJsonApi\Laravel\Http\Controllers\Actions;

class PlanController extends BaseMediaController
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
        return Plan::class;
    }
}
