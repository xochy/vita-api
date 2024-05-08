<?php

namespace App\Models;

use App\Models\Traits\Mutators\GoalMutators;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Goal extends Model
{
    use HasFactory, HasSlug, GoalMutators;

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
     * Get the plans associated with the goal.
     *
     * This function establishes a hasMany relationship between Goal and Plan.
     * It means that each Goal has many Plans.
     *
     * @return HasMany
     */
    public function plans(): HasMany
    {
        return $this->hasMany(Plan::class);
    }

    /**
     * Get the translations associated with the goal.
     *
     * This function establishes a morphMany relationship between Goal and Translation.
     * It means that each Goal has many Translations.
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
     * Apply the scope related with name.
     *
     * @param Builder $builder
     * @param string $value
     * @return void
     */
    public function scopeName(Builder $query, $value): void
    {
        $query->where('name', 'LIKE', "%{$value}%");
    }

    /**
     * Apply the scope related with description.
     *
     * @param Builder $builder
     * @param string $value
     * @return Builder
     */
    public function scopeDescription(Builder $query, $value): void
    {
        $query->where('description', 'LIKE', "%{$value}%");
    }

    /**
     * Apply the scope related with search function.
     *
     * @param Builder $builder
     * @param string $values
     * @return Builder
     */
    public function scopeSearch(Builder $query, $values): void
    {
        foreach (Str::of($values)->explode(' ') as $value) {
            $query->orWhere('name', 'LIKE', "%{$value}%")
                ->orWhere('description', 'LIKE', "%{$value}%");
        }
    }
}
