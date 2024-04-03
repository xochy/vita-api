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
}
