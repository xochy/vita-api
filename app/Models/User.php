<?php

namespace App\Models;

use App\Models\Traits\Mutators\UserMutators;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class User extends Authenticatable
{
    use HasApiTokens,
        HasFactory,
        HasSlug,
        Notifiable,
        HasRoles,
        SoftDeletes,
        UserMutators;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', 'email', 'password', 'age',
        'gender', 'system', 'weight', 'height', 'deleted_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'weight' => 'float',
            'height' => 'float',
        ];
    }

    /**
     * Get the options for generating the slug.
     *
     * @return SlugOptions
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    /* -------------------------------------------------------------------------- */
    /*                                Relationships                               */
    /* -------------------------------------------------------------------------- */

    public function relatedRoles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'model_has_roles', 'model_id', 'role_id');
    }

    /**
     * Get the plans associated with the user.
     *
     * This function establishes a belongsToMany relationship between User and Plan.
     * It means that each User belongs to many Plans.
     *
     * @return BelongsToMany
     */
    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(Plan::class);
    }

    /* -------------------------------------------------------------------------- */
    /*                                   Scopes                                   */
    /* -------------------------------------------------------------------------- */

    /**
     * Scope a query to only include users with a given name.
     *
     * @param Builder $query
     * @param string $value
     * @return void
     */
    public function scopeName(Builder $query, string $value): void
    {
        $query->where('name', 'like', "%$value%");
    }
}
