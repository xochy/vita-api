<?php

namespace App\JsonApi\V1;

use App\Models\Routine;
use Illuminate\Support\Facades\Auth;
use LaravelJsonApi\Core\Server\Server as BaseServer;

class Server extends BaseServer
{

    /**
     * The base URI namespace for this server.
     *
     * @var string
     */
    protected string $baseUri = '/api/v1';

    /**
     * Bootstrap the server when it is handling an HTTP request.
     *
     * @return void
     */
    public function serving(): void
    {
        Auth::shouldUse('sanctum');
    }

    /**
     * Get the server's list of schemas.
     *
     * @return array
     */
    protected function allSchemas(): array
    {
        return [
            Categories\CategorySchema::class,
            Frequencies\FrequencySchema::class,
            Goals\GoalSchema::class,
            Muscles\MuscleSchema::class,
            PhysicalConditions\PhysicalConditionSchema::class,
            Subcategories\SubcategorySchema::class,
            Workouts\WorkoutSchema::class,
            Routines\RoutineSchema::class,
            Plans\PlanSchema::class,
        ];
    }
}
