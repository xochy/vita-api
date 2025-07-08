<?php

namespace App\JsonApi\V1\Pitvots;

use function optional;

class PriorityPivot
{

    /**
     * Get the pivot attributes.
     *
     * @param $parent
     * @param $related
     * @return array
     */
    public function __invoke($parent, $related): array
    {
        return [
            'priority' => optional($related->pivot)->priority ?? 0,
        ];
    }

}
