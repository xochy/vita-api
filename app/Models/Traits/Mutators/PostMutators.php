<?php

namespace App\Models\Traits\Mutators;

use App\Traits\Translation;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait PostMutators
{
    use Translation;

    /**
     * Get the title of the post.
     *
     * @return string
     */
    public function title(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => $this->translation('title', $value),
        );
    }

    /**
     * Get the content of the post.
     *
     * @return string
     */
    public function content(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => $this->translation('content', $value),
        );
    }
}
