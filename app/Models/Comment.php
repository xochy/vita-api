<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Comment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'post_id',
        'user_id',
        'content',
    ];

    /**
     * Boot the model and assign user_id automatically when creating a post.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Automatically set the user_id to the currently authenticated user
        static::creating(function ($comment) {
            if (Auth::check() && !$comment->user_id) {
                $comment->user_id = Auth::id();
            }
        });
    }

    /* -------------------------------------------------------------------------- */
    /*                                Relationships                               */
    /* -------------------------------------------------------------------------- */

    /**
     * Get the post associated with this comment.
     *
     * This function establishes a BelongsTo relationship between Comment and Post.
     * It means that each Comment has a Post associated with it.
     *
     * @var array
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the user associated with this comment.
     *
     * This function establishes a BelongsTo relationship between Comment and User.
     * It means that each Comment has a User associated with it.
     *
     * @var array
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
