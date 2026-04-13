<?php

namespace App\Enum;

enum HumidityRequirement: string
{
    case low = 'niedrig';
    case medium = 'mittel';
    case hight = 'hoch';
}
