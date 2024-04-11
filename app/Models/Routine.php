<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Routine extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    /* -------------------------------------------------------------------------- */
    /*                                Relationships                               */
    /* -------------------------------------------------------------------------- */

    /**
     * Get the workouts associated with the routine.
     *
     * This function establishes a belongsToMany relationship between Routine and Workout.
     * It means that each Routine belongs to many Workouts.
     *
     * @return BelongsToMany
     */
    public function workouts()
    {
        return $this->belongsToMany(Workout::class)
            ->withPivot('series', 'repetitions', 'time')
            ->using(RoutineWorkout::class)
            ->as('routine_workout');
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
    public function scopeName(Builder $query, $value)
    {
        $query->where('name', 'LIKE', "%{$value}%");
    }

    /**
     * Apply the scope related with search function.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param array
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch(Builder $query, $values)
    {
        foreach (Str::of($values)->explode(' ') as $value) {

            $query->orWhere('name', 'LIKE', "%{$value}%");
        }
    }
}
