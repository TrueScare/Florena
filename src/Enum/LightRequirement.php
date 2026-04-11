<?php

namespace App\Enum;

enum LightRequirement: string
{
    case shady = 'schattig';
    case halfshady = 'halbschattig';
    case bright = 'hell';
    case sunny = 'sonnig';
}
