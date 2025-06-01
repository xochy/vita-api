<?php

namespace App\Models;

use App\Models\Traits\Mutators\EquipmentMutators;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str as SupportStr;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Equipment extends Model implements HasMedia
{
    use HasFactory, HasSlug, InteractsWithMedia, EquipmentMutators;

    protected $table = 'equipments';

    protected $fillable = ['name', 'description'];

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    /**
     * Get the translations associated with the equipment.
     *
     * This function establishes a morphMany relationship between Equipment and Translation.
     * It means that each Equipment has many Translations.
     *
     * @return MorphMany
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translationable');
    }

    /**
     * Scope a query to only include equipment with a given name.
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
     * Scope a query to only include equipment with a given description.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $value
     * @return void
     */
    public function scopeDescription(Builder $query, string $value): void
    {
        $query->where('description', 'LIKE', "%{$value}%");
    }

    /**
     * Scope a query to search for equipment by name or description.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $values
     * @return void
     */
    public function scopeSearch(Builder $query, string $values): void
    {
        foreach (SupportStr::of($values)->explode(' ') as $value) {
            $query->orWhere('name', 'LIKE', "%{$value}%")
                ->orWhere('description', 'LIKE', "%{$value}%");
        }
    }
}
