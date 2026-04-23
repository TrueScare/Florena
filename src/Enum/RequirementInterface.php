<?php

namespace App\Enum;

interface RequirementInterface
{
    public function matches(self $other): bool;
}
