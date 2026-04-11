<?php

namespace App\Enum;

enum Status: string
{
    case planned = 'geplant';
    case in_progress = 'in durchführung';
    case finished = 'abgeschlossen';
}
