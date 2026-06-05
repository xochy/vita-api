<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use LaravelJsonApi\Laravel\Http\Controllers\Actions;

class EquipmentController extends BaseMediaController
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
        return Equipment::class;
    }
}
