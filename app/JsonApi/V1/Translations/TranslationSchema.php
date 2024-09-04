<?php

namespace App\JsonApi\V1\Translations;

use App\Models\Translation;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Fields\Relations\MorphTo;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;

class TranslationSchema extends Schema
{

    /**
     * Determine if the resource is authorizable.
     *
     * @return bool
     */
    public function authorizable(): bool
    {
        return false;
    }
    
    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    public static string $model = Translation::class;

    /**
     * Get the resource fields.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            ID::make(),
            Str::make('column'),
            Str::make('locale'),
            Str::make('translation'),
            DateTime::make('createdAt')->sortable()->readOnly(),
            DateTime::make('updatedAt')->sortable()->readOnly(),

            // Relationships
            MorphTo::make('translationable')->types(
                'plans',
                'goals',
                'muscles',
                'workouts',
                'routines',
                'categories',
                'variations',
                'frequencies',
                'physical-conditions',
            )
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
