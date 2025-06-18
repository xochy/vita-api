<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProposeRoutineRequest;
use App\Services\RoutineGeneratorService;
use Illuminate\Http\Request;
use LaravelJsonApi\Core\Responses\DataResponse;
use LaravelJsonApi\Laravel\Http\Controllers\Actions;

class RoutineController extends Controller
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

    protected $routineGeneratorService;

    public function __construct(RoutineGeneratorService $routineGeneratorService)
    {
        $this->routineGeneratorService = $routineGeneratorService;
    }

    public function propose(ProposeRoutineRequest $request)
    {
        $data = $request->getRoutineData();

        $workouts = $this->routineGeneratorService->propose(
            $data['user_id'],
            $data['gender'],
            $data['age'],
            $data['goal'],
            $data['level'],
            $data['equipment_ids'],
            $data['muscle_ids']
        );

        return DataResponse::make($workouts);
    }

    public function generate(Request $request)
    {
        $routine = $this->routineGeneratorService->generate(
            $request['data']['name'],
            $request['data']['workout_ids']
        );

        return DataResponse::make($routine);
    }
}
