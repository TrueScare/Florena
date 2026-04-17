<?php

namespace App\Enum;

enum PaginationOrder: string
{
    case NAME_ASC = 'nameAsc';
    case NAME_DESC = 'nameDesc';
    case LOCATION_ASC = 'locationAsc';
    case LOCATION_DESC = 'locationDesc';
}
