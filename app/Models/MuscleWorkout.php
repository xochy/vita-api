<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MuscleWorkout extends Pivot
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['priority'];

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;
}
