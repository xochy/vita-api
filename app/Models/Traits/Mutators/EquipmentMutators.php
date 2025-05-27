<?php

namespace App\Models\Traits\Mutators;

use App\Traits\Translation;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait EquipmentMutators
{
    use Translation;

    /**
     * Get the name of the equipment.
     *
     * @return string
     */
    public function name(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => $this->translation('name', $value),
        );
    }

    /**
     * Get the description of the equipment.
     *
     * @return string
     */
    public function description(): Attribute
    {
        return Attribute::make(
            get: fn(string $value) => $this->translation('description', $value),
        );
    }
}
