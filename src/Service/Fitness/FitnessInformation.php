<?php

namespace App\Service\Fitness;

use App\Entity\RequirementsEntityInterface;
use App\Enum\FitnessStatus;
use App\Enum\RequirementInterface;
use Symfony\Component\Serializer\Attribute\Groups;

class FitnessInformation
{
    #[Groups('fitnessinformation:ref')]
    /** @var FitnessStatus $status */
    private FitnessStatus $status;

    #[Groups('fitnessinformation:ref')]
    /** @var array<RequirementInterface, RequirementInterface> $missmatches */
    private array $missmatches;

    public function __construct(
        #[Groups('fitnessinformation:ref')]
        private RequirementsEntityInterface $entity,
        #[Groups('fitnessinformation:ref')]
        private string                      $entityClass
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
     * The missmatch array structure is as follows:
     * [
     *    RequirementInterface::class => [
     *          'fromEntity' => 'toEntity'
     *      ]
     * ]
     *
     * @param array<array<RequirementInterface>> $match
     * @return void
     */
    public function addMissmatch(array $match): void
    {
        $this->missmatches = array_merge($this->missmatches, $match);
    }

    public function getEntity(): RequirementsEntityInterface
    {
        return $this->entity;
    }

    public function setEntity(RequirementsEntityInterface $entity): void
    {
        $this->entity = $entity;
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    public function setEntityClass(string $entityClass): void
    {
        $this->entityClass = $entityClass;
    }
}
