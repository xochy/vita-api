<?php

namespace App\Models\Traits\Mutators;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait UserMutators
{
    /**
     * Get the BMI of the user.
     *
     * @return Attribute
     */
    public function bmi(): Attribute
    {
        $bmi = null;

        // Validate the weight and height values to avoid division by zero
        if ($this->weight > 0 && $this->height > 0) {
            $bmi = $this->weight / ($this->height * $this->height);
        }

        return Attribute::make(
            get: fn () => $bmi,
        );
    }
}
