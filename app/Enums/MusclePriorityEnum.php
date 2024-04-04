<?php

namespace App\Enums;

enum MusclePriorityEnum: string
{
    case PRINCIPAL = 'principal';
    case SECONDARY = 'secondary';
    case ANTAGONIST = 'antagonist';

    public static function getAllValues(): array
    {
        return array_column(MusclePriorityEnum::cases(), 'value');
    }
}
