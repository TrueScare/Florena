<?php

namespace App\Enum;

enum TemperatureRequirement: string
{
    case cool = 'kühl (< 18° C)';
    case normal = 'normal (18–24°C)';
    case warm = 'warm (> 24°C)';
}
