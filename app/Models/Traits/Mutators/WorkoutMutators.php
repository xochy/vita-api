<?php

namespace App\Models\Traits\Mutators;

use App\Traits\Translation;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait WorkoutMutators
{
    use Translation;

    /**
     * Get the name of the workout.
     *
     * @return string
     */
    public function name(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => $this->translation('name', $value),
        );
    }

    /**
     * Get the performance of the workout.
     *
     * @return string
     */
    public function performance(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => $this->translation('performance', $value),
        );
    }

    /**
     * Get the comments of the workout. If the value is null, return an empty string.
     *
     * @return string
     */
    public function comments(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $this->translation('comments', $value),
        );
    }

    /**
     * Get the corrections of the workout. If the value is null, return an empty string.
     *
     * @return string
     */
    public function corrections(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $this->translation('corrections', $value),
        );
    }

    /**
     * Get the warnings of the workout. If the value is null, return an empty string.
     *
     * @return string
     */
    public function warnings(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $this->translation('warnings', $value),
        );
    }
}
