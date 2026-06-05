<?php

namespace App\Models;

use App\Models\Traits\Mutators\PostMutators;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Post extends Model implements HasMedia
{
    use HasFactory, HasSlug, PostMutators, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'published_at',
    ];


    /**
     * The attributes that are castable.
     *
     * @var array
     */
    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * Get the options for generating the slug.
     *
     * @return SlugOptions
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    /**
     * Boot the model and assign user_id automatically when creating a post.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Automatically assign the authenticated user's ID to the post when creating it
        static::creating(function ($post) {
            if (Auth::check() && !$post->user_id) {
                $post->user_id = Auth::id();
            }
        });
    }

    /* -------------------------------------------------------------------------- */
    /*                                Relationships                               */
    /* -------------------------------------------------------------------------- */

    /**
     * Get the user associated with this post.
     *
     * This function establishes a BelongsTo relationship between Post and User.
     * It means that each Post has a User associated with it.
     *
     * @return BelongsTo
     */
    public function User(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the comments associated with this post.
     *
     * This function establishes a HasMany relationship between Post and Comment.
     * It means that each Post can have multiple Comments associated with it.
     *
     * @return HasMany<Comment, Post>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the translations associated with the post.
     *
     * This function establishes a morphMany relationship between Post and Translation.
     * It means that each Post has many Translations.
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
     * Scope a query to only include post with a given title.
     *
     * @param Builder $query
     * @param string $value
     * @return void
     */
    public function scopeTitle(Builder $query, string $value): void
    {
        $query->where('title', 'LIKE', "%{$value}%");
    }

    /**
     * Scope a query to only include posts that are published.
     *
     * @param Builder $query
     * @return void
     */

    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope a query to only include posts that are not published.
     *
     * @param Builder $query
     * @return void
     */
    public function scopeNotPublished(Builder $query): void
    {
        $query->whereNull('published_at')
            ->orWhere('published_at', '>', now());
    }

    /**
     * Scope a query to only include posts by a specific user.
     *
     * @param Builder $query
     * @param int $userId
     * @return void
     */
    public function scopeByUser(Builder $query, int $userId): void
    {
        $query->where('user_id', $userId);
    }

    /**
     * Scope a query to only include posts with a specific content.
     *
     * @param Builder $query
     * @param string $value
     * @return void
     */
    public function scopeContent(Builder $query, string $value): void
    {
        $query->where('content', 'LIKE', "%{$value}%");
    }

    /**
     * Scope a query to search posts by title or content.
     *
     * @param Builder $query
     * @param string $values
     * @return void
     */
    public function scopeSearch(Builder $query, string $values): void
    {
        foreach (explode(' ', $values) as $value) {
            $query->orWhere('title', 'LIKE', "%{$value}%")
                ->orWhere('content', 'LIKE', "%{$value}%");
        }
    }
}
