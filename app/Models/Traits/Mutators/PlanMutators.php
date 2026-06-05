<?php

namespace App\Models\Traits\Mutators;

use App\Traits\Translation;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait PlanMutators
{
    use Translation;

    /**
     * Get the name of the plan.
     *
     * @return string
     */
    public function name(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => $this->translation('name', $value),
        );
    }
}
