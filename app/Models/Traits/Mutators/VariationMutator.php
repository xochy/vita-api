<?php

namespace App\Models\Traits\Mutators;

use App\Traits\Translation;
use Illuminate\Database\Eloquent\Casts\Attribute;


trait VariationMutator
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
}
