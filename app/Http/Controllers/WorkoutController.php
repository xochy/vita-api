<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\JsonApi\V1\Workouts\WorkoutRequest;
use App\Models\Workout;
use LaravelJsonApi\Laravel\Http\Controllers\Actions;

class WorkoutController extends Controller
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

    public function saved(Workout $workout, WorkoutRequest $request): void
    {
        if (!isset($request->data['attributes']['image'])) {
            return;
        }

        $workout
            ->addMedia($request->data['attributes']['image'])
            ->toMediaCollection();
    }
}
