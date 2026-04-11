<?php

namespace App\Enum;

enum CareType: string
{
    case fertilice = 'düngen';
    case water = 'gießen';
    case repot = 'umtopfen';
}
