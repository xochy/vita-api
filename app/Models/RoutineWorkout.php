<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class RoutineWorkout extends Pivot
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['series', 'repetitions', 'time'];

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;
}
