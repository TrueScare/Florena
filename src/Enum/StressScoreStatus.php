<?php

namespace App\Enum;

enum StressScoreStatus: string
{
    case healthy = 'gesund';
    case slightlyStressed = 'leicht gestresst';
    case problem = 'Pflegeproblem';
}
