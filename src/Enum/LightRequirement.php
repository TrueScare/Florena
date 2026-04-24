<?php

namespace App\Enum;

enum LightRequirement: string implements RequirementInterface
{
    case shady = 'schattig';
    case halfshady = 'halbschattig';
    case bright = 'hell';
    case sunny = 'sonnig';

    public function matches(RequirementInterface $other): bool
    {
        return $this === $other;
    }
}
