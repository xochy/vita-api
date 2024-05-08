<?php

namespace App\Models;

use App\Models\Traits\Mutators\WorkoutMutators;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
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
        'name', 'performance', 'comments', 'corrections', 'warnings'
    ];

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
     * Get the subcategory associated with the workout.
     *
     * This function establishes a belongsTo relationship between Workout and Subcategory.
     * It means that each Workout belongs to one Subcategory.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    /**
     * Get the muscles associated with the workout.
     *
     * This function establishes a belongsToMany relationship between Workout and Muscle.
     * It means that each Workout belongs to many Muscles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function muscles()
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
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
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
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param string
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeName(Builder $query, $value): void
    {
        $query->where('name', 'LIKE', "%{$value}%");
    }

    /**
     * Apply the scope related with performance.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param string
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePerformance(Builder $query, $value): void
    {
        $query->where('performance', 'LIKE', "%{$value}%");
    }

    /**
     * Apply the scope related with comments.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param string
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeComments(Builder $query, $value): void
    {
        $query->where('comments', 'LIKE', "%{$value}%");
    }

    /**
     * Apply the scope related with corrections.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param string
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCorrections(Builder $query, $value): void
    {
        $query->where('corrections', 'LIKE', "%{$value}%");
    }

    /**
     * Apply the scope related with warnings.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param string
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWarnings(Builder $query, $value): void
    {
        $query->where('warnings', 'LIKE', "%{$value}%");
    }
}
