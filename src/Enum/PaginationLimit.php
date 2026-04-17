<?php

namespace App\Enum;

enum PaginationLimit: int
{
    case ten = 10;
    case twentyfive = 25;
    case fifty = 50;
}
