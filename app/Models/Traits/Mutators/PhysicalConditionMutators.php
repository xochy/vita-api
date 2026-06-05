<?php

namespace App\Models\Traits\Mutators;
use App\Traits\Translation;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait PhysicalConditionMutators
{
    use Translation;

    /**
     * Get the name of the physical condition.
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
     * Get the description of the physical condition.
     *
     * @return string
     */
    public function description(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => $this->translation('description', $value),
        );
    }
}
