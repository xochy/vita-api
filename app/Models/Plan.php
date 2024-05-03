<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Plan extends Model
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
     * Get the goal associated with the plan.
     *
     * This function establishes a belongsTo relationship between Plan and Goal.
     * It means that each Plan belongs to one Goal.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function goal()
    {
        return $this->belongsTo(Goal::class);
    }

    /**
     * Get the frequency associated with the plan.
     *
     * This function establishes a belongsTo relationship between Plan and Frequency.
     * It means that each Plan belongs to one Frequency.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function frequency()
    {
        return $this->belongsTo(Frequency::class);
    }

    /**
     * Get the physical condition associated with the plan.
     *
     * This function establishes a belongsTo relationship between Plan and PhysicalCondition.
     * It means that each Plan belongs to one PhysicalCondition.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function physicalCondition()
    {
        return $this->belongsTo(PhysicalCondition::class);
    }

    /**
     * Get the routines associated with the plan.
     *
     * This function establishes a belongsToMany relationship between Plan and Routine.
     * It means that each Plan belongs to many Routines.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function routines()
    {
        return $this->belongsToMany(Routine::class);
    }

    /**
     * Get the users associated with the plan.
     *
     * This function establishes a belongsToMany relationship between Plan and User.
     * It means that each Plan belongs to many Users.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
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
     * Apply the scope related with search function.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param string
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch(Builder $query, $values): void
    {
        foreach (Str::of($values)->explode(' ') as $value) {
            $query->orWhere('name', 'LIKE', "%{$value}%");
        }
    }
}
