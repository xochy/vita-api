<?php

namespace App\Models;

use App\Models\Traits\Mutators\PlanMutators;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Plan extends Model implements HasMedia
{
    use HasFactory, HasSlug, PlanMutators, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * Get the options for generating the slug.
     *
     * @return SlugOptions
     */
    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    /* -------------------------------------------------------------------------- */
    /*                                Relationships                               */
    /* -------------------------------------------------------------------------- */

    /**
     * Get the goal associated with the plan.
     *
     * This function establishes a belongsTo relationship between Plan and Goal.
     * It means that each Plan belongs to one Goal.
     *
     * @return BelongsTo
     */
    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    /**
     * Get the frequency associated with the plan.
     *
     * This function establishes a belongsTo relationship between Plan and Frequency.
     * It means that each Plan belongs to one Frequency.
     *
     * @return BelongsTo
     */
    public function frequency(): BelongsTo
    {
        return $this->belongsTo(Frequency::class);
    }

    /**
     * Get the physical condition associated with the plan.
     *
     * This function establishes a belongsTo relationship between Plan and PhysicalCondition.
     * It means that each Plan belongs to one PhysicalCondition.
     *
     * @return BelongsTo
     */
    public function physicalCondition(): BelongsTo
    {
        return $this->belongsTo(PhysicalCondition::class);
    }

    /**
     * Get the routines associated with the plan.
     *
     * This function establishes a belongsToMany relationship between Plan and Routine.
     * It means that each Plan belongs to many Routines.
     *
     * @return BelongsToMany
     */
    public function routines(): BelongsToMany
    {
        return $this->belongsToMany(Routine::class);
    }

    /**
     * Get the users associated with the plan.
     *
     * This function establishes a belongsToMany relationship between Plan and User.
     * It means that each Plan belongs to many Users.
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Get the translations associated with the plan.
     *
     * This function establishes a morphMany relationship between Plan and Translation.
     * It means that each Plan has many Translations.
     *
     * @return MorphMany
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    /* -------------------------------------------------------------------------- */
    /*                                   Scopes                                   */
    /* -------------------------------------------------------------------------- */

    /**
     * Apply the scope related with name.
     *
     * @param Builder $query
     * @param string $value
     * @return void
     */
    public function scopeName(Builder $query, string $value): void
    {
        $query->where('name', 'LIKE', "%{$value}%");
    }

    /**
     * Apply the scope related with search function.
     *
     * @param Builder $query
     * @param string $values
     * @return void
     */
    public function scopeSearch(Builder $query, string $values): void
    {
        foreach (Str::of($values)->explode(' ') as $value) {
            $query->orWhere('name', 'LIKE', "%{$value}%");
        }
    }
}
