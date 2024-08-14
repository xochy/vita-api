<?php

namespace App\Models;

use App\Models\Traits\Mutators\VariationMutator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Variation extends Model implements HasMedia
{
    use HasFactory, HasSlug, VariationMutator, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'performance',
    ];

    /**
     * Get the options for generating the slug.
     *
     * @return SlugOptions
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    /* -------------------------------------------------------------------------- */
    /*                                Relationships                               */
    /* -------------------------------------------------------------------------- */

    /**
     * Get the workout associated with the variation.
     *
     * This function establishes a belongsTo relationship between variation and workout.
     * It means that each variation belongs to one workout.
     *
     * @return BelongsTo
     */
    public function workout(): BelongsTo
    {
        return $this->belongsTo(Workout::class);
    }

    /**
     * Get the muscles associated with the variation.
     *
     * This function establishes a belongsToMany relationship between variation and muscle.
     * It means that each variation belongs to many muscles.
     *
     * @return BelongsToMany
     */
    public function muscles(): BelongsToMany
    {
        return $this->belongsToMany(Muscle::class);
    }

    /**
     * Get the translations associated with the workout.
     *
     * This function establishes a morphMany relationship between Workout and Translation.
     * It means that each Workout has many Translations.
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
     * @param Builder $query
     * @param string $value
     * @return void
     */
    public function scopeName(Builder $query, string $value): void
    {
        $query->where('name', 'LIKE', "%{$value}%");
    }

    /**
     * Apply the scope related with performance.
     *
     * @param Builder $query
     * @param string $value
     * @return void
     */
    public function scopePerformance(Builder $query, string $value): void
    {
        $query->where('performance', 'LIKE', "%{$value}%");
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
                ->orWhere('performance', 'LIKE', "%{$value}%");
        }
    }
}
