<?php

namespace App\Enum;

enum HumidityRequirement: string
{
    case low = 'niedrig';
    case medium = 'middel';
    case hight = 'hoch';
}
