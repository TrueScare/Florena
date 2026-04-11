<?php

namespace App\Enum;

enum TemperatureRequirement: string
{
    case cool = 'kühl';
    case normal = 'normal';
    case warm = 'warm';
}
