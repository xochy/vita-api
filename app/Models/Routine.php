<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * Define belongs-to-many workouts relationship.
     *
     * @return BelongsToMany
     */
    public function workouts()
    {
        return $this->belongsToMany(Workout::class)
            ->withPivot('series', 'repetitions', 'time')
            ->using(MuscleWorkout::class)
            ->as('muscle_workout');
    }
}
