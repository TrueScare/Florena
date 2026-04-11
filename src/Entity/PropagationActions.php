<?php

namespace App\Entity;

use App\Enum\PropagationMethod;
use App\Enum\Status;
use App\Repository\PropagationActionsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PropagationActionsRepository::class)]
class PropagationActions
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: PropagationMethod::class)]
    private ?PropagationMethod $method = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $planned_date = null;

    #[ORM\Column(enumType: Status::class)]
    private ?Status $status = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMethod(): ?PropagationMethod
    {
        return $this->method;
    }

    public function setMethod(PropagationMethod $method): static
    {
        $this->method = $method;

        return $this;
    }

    public function getPlannedDate(): ?\DateTimeImmutable
    {
        return $this->planned_date;
    }

    public function setPlannedDate(\DateTimeImmutable $planned_date): static
    {
        $this->planned_date = $planned_date;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(Status $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }
}
