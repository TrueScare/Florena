<?php

namespace App\Enum;

enum HumidityRequirement: string implements RequirementInterface
{
    case low = 'niedrig';
    case medium = 'mittel';
    case hight = 'hoch';
    public function matches(RequirementInterface $other): bool
    {
        return $this === $other;
    }
}
