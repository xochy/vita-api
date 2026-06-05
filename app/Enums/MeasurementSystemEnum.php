<?php

namespace App\Enums;

enum MeasurementSystemEnum: string
{
    case METRIC = 'metric';
    case IMPERIAL = 'imperial';

    public static function getAllValues(): array
    {
        return array_column(MeasurementSystemEnum::cases(), 'value');
    }
}
