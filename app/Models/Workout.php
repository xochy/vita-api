<?php

namespace App\Models;

use App\Models\Traits\Mutators\WorkoutMutators;
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

class Workout extends Model implements HasMedia
{
    use HasFactory, HasSlug, WorkoutMutators, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'group',
        'name',
        'performance',
        'comments',
        'corrections',
        'warnings'
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
     * Get the category associated with the workout.
     *
     * This function establishes a belongsTo relationship between workout and category.
     * It means that each workout belongs to one category.
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the muscles associated with the workout.
     *
     * This function establishes a belongsToMany relationship between Workout and Muscle.
     * It means that each Workout belongs to many Muscles.
     *
     * @return BelongsToMany
     */
    public function muscles(): BelongsToMany
    {
        return $this->belongsToMany(Muscle::class)
            ->withPivot('priority')
            ->using(MuscleWorkout::class)
            ->as('muscle_workout');
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
     * Apply the scope related with comments.
     *
     * @param Builder $query
     * @param string $value
     * @return void
     */
    public function scopeComments(Builder $query, string $value): void
    {
        $query->where('comments', 'LIKE', "%{$value}%");
    }

    /**
     * Apply the scope related with corrections.
     *
     * @param Builder $query
     * @param string $value
     * @return void
     */
    public function scopeCorrections(Builder $query, string $value): void
    {
        $query->where('corrections', 'LIKE', "%{$value}%");
    }

    /**
     * Apply the scope related with warnings.
     *
     * @param Builder $query
     * @param string $value
     * @return void
     */
    public function scopeWarnings(Builder $query, string $value): void
    {
        $query->where('warnings', 'LIKE', "%{$value}%");
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
                ->orWhere('performance', 'LIKE', "%{$value}%")
                ->orWhere('comments', 'LIKE', "%{$value}%")
                ->orWhere('corrections', 'LIKE', "%{$value}%")
                ->orWhere('warnings', 'LIKE', "%{$value}%");
        }
    }
}
