<?php

namespace App\Models;

use App\Models\Traits\Mutators\CategoryMutators;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Category extends Model
{
    use HasFactory, HasSlug, CategoryMutators;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description'];

    /**
     * Get the options for generating the slug.
     *
     * @return SlugOptions
     */
    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    /* -------------------------------------------------------------------------- */
    /*                                Relationships                               */
    /* -------------------------------------------------------------------------- */

    /**
     * Get the workouts associated with the category.
     *
     * This function establishes a hasMany relationship between Category and Workout.
     * It means that each Category has many Workouts.
     *
     * @return HasMany
     */
    public function workouts(): HasMany
    {
        return $this->hasMany(Workout::class);
    }

    /**
     * Get the translations associated with the category.
     *
     * This function establishes a morphMany relationship between Category and Translation.
     * It means that each Category has many Translations.
     *
     * @return MorphMany
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    /* -------------------------------------------------------------------------- */
    /*                                   Scopes                                   */
    /* -------------------------------------------------------------------------- */

    /**
     * Scope a query to only include users with a given name.
     *
     * @param Builder $query
     * @param string $value
     * @return void
     */
    public function scopeName(Builder $query, string $value): void
    {
        $query->where('name', 'LIKE', "%{$value}%");
    }

    /**
     * Scope a query to only include users with a given description.
     *
     * @param Builder $query
     * @param string $value
     * @return void
     */
    public function scopeDescription(Builder $query, string $value): void
    {
        $query->where('description', 'LIKE', "%{$value}%");
    }

    /**
     * Scope a query to only include users with a given search term.
     *
     * @param Builder $query
     * @param string $values
     * @return void
     */
    public function scopeSearch(Builder $query, string $values): void
    {
        foreach (Str::of($values)->explode(' ') as $value) {

            $query->orWhere('name', 'LIKE', "%{$value}%")
                ->orWhere('description', 'LIKE', "%{$value}%");
        }
    }
}
