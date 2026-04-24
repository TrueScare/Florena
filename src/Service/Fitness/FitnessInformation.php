<?php

namespace App\Service\Fitness;

use App\Enum\FitnessStatus;
use App\Enum\RequirementInterface;

class FitnessInformation
{
    /** @var FitnessStatus $status */
    private FitnessStatus $status;

    /** @var array<RequirementInterface, RequirementInterface> $missmatches */
    private array $missmatches;

    public function __construct(
        private int $locationId
    )
    {
        $this->missmatches = [];
    }

    public function getStatus(): FitnessStatus
    {
        return $this->status;
    }

    public function setStatus(FitnessStatus $status): void
    {
        $this->status = $status;
    }

    public function getMissmatches(): array
    {
        return $this->missmatches;
    }

    public function setMissmatches(array $missmatches): void
    {
        $this->missmatches = $missmatches;
    }

    /**
     * @param array<RequirementInterface, RequirementInterface> $match
     * @return void
     */
    public function addMissmatch(array $match): void
    {
       $this->missmatches[] = $match;
    }

    public function getLocationId(): int
    {
        return $this->locationId;
    }

    public function setLocationId(int $locationId): void
    {
        $this->locationId = $locationId;
    }
}
