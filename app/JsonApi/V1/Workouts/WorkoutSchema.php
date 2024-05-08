<?php

namespace App\JsonApi\V1\Workouts;

use App\JsonApi\V1\Helpers\BelongsToMany;
use App\Models\Workout;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Fields\Relations\BelongsTo;
use LaravelJsonApi\Eloquent\Filters\Scope;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;

class WorkoutSchema extends Schema
{

    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    public static string $model = Workout::class;

    /**
     * Get the resource fields.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            ID::make(),
            Str::make('name')->sortable(),
            Str::make('performance'),
            Str::make('comments'),
            Str::make('corrections'),
            Str::make('warnings'),
            Str::make('image')->extractUsing(
                static fn($model) => $model->getFirstMediaUrl()
            ),
            Str::make('slug')->readOnly(),
            DateTime::make('createdAt')->sortable()->readOnly(),
            DateTime::make('updatedAt')->sortable()->readOnly(),

            // Relationships
            BelongsTo::make('subcategory'),
            BelongsToMany::make('muscles')->fields(
                [
                    'priority'
                ]
            ),
            BelongsToMany::make('routines'),
        ];
    }

    /**
     * Get the resource filters.
     *
     * @return array
     */
    public function filters(): array
    {
        return [
            WhereIdIn::make($this),
            Scope::make('name'),
            Scope::make('performance'),
            Scope::make('comments'),
            Scope::make('corrections'),
            Scope::make('warnings'),
        ];
    }

    /**
     * Get the resource paginator.
     *
     * @return Paginator|null
     */
    public function pagination(): ?Paginator
    {
        return PagePagination::make();
    }
}
