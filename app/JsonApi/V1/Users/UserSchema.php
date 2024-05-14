<?php

namespace App\JsonApi\V1\Users;

use App\JsonApi\V1\Helpers\BelongsToMany;
use App\Models\User;
use LaravelJsonApi\Eloquent\Contracts\Paginator;
use LaravelJsonApi\Eloquent\Fields\DateTime;
use LaravelJsonApi\Eloquent\Fields\Str;
use LaravelJsonApi\Eloquent\Fields\ID;
use LaravelJsonApi\Eloquent\Fields\Number;
use LaravelJsonApi\Eloquent\Fields\SoftDelete;
use LaravelJsonApi\Eloquent\Filters\Scope;
use LaravelJsonApi\Eloquent\Filters\WhereIdIn;
use LaravelJsonApi\Eloquent\Filters\WithTrashed;
use LaravelJsonApi\Eloquent\Pagination\PagePagination;
use LaravelJsonApi\Eloquent\Schema;
use LaravelJsonApi\Eloquent\SoftDeletes;

class UserSchema extends Schema
{
    use SoftDeletes;

    /**
     * The model the schema corresponds to.
     *
     * @var string
     */
    public static string $model = User::class;

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
            Str::make('email')->sortable(),
            DateTime::make('emailVerifiedAt')->sortable(),
            Str::make('password')->hidden(),
            Str::make('password_confirmation')->hidden(),
            Number::make('age')->sortable(),
            Str::make('gender')->sortable(),
            Str::make('system'),
            Number::make('weight')->sortable(),
            Number::make('height')->sortable(),
            Str::make('bmi')->sortable()->readOnly(),
            DateTime::make('createdAt')->sortable()->readOnly(),
            DateTime::make('updatedAt')->sortable()->readOnly(),
            SoftDelete::make('deletedAt'),

            // Relationships
            BelongsToMany::make('plans'),
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
            WithTrashed::make('with-trashed'),

            Scope::make('name'),
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
