<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateRoutineRequest;
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
        $data = $request->getProposedData();

        $workouts = $this->routineGeneratorService->propose(
            $data['level'],
            $data['equipment_ids'],
            $data['muscle_ids'],
        );

        return DataResponse::make($workouts);
    }

    public function generate(GenerateRoutineRequest $request)
    {
        $routine = $this->routineGeneratorService->generate(
            $request['data']['user_id'],
            $request['data']['name'],
            $request['data']['gender'],
            $request['data']['age'],
            $request['data']['goal'],
            $request['data']['workout_ids']
        );

        return DataResponse::make($routine);
    }
}
