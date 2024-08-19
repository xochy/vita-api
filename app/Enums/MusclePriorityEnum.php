<?php

namespace App\Enums;

enum MusclePriorityEnum: string
{
    case PRINCIPAL = 'Principal';
    case SECONDARY = 'Secondary';
    case ANTAGONIST = 'Antagonist';

    public static function getAllValues(): array
    {
        return array_column(MusclePriorityEnum::cases(), 'value');
    }
}
