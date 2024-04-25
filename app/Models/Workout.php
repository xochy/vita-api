<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Workout extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'performance', 'comments', 'corrections', 'warnings'
    ];

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
