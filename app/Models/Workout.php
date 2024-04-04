<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workout extends Model
{
    use HasFactory;

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
     * Define belongs-to subcategory relationship.
     *
     * @return BelongsTo
     */
    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    /**
     * Define belongs-to-many muscles relationship.
     *
     * @return BelongsToMany
     */
    public function muscles()
    {
        return $this->belongsToMany(Muscle::class)
            ->withPivot('priority')
            ->using(MuscleWorkout::class)
            ->as('muscle_workout');
    }
}
