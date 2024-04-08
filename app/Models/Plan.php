<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
