<?php

namespace App\Enum;

enum FitnessStatus: string
{
    case perfect = "geeignet";
    case partly = "teilweise geeignet";
    case none = "nicht geeignet";
}
