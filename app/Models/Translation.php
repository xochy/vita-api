<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Translation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['locale', 'column', 'translation', 'translationable_id', 'translationable_type'];

    /**
     * Get the parent translationable model.
     */
    public function translationable(): MorphTo
    {
        return $this->morphTo();
    }
}
