<?php

namespace App\Entity;

use App\Repository\TaskAssignmentsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskAssignmentsRepository::class)]
class TaskAssignments
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $start_date = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $end_date = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $assigned_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $responded_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->start_date;
    }

    public function setStartDate(\DateTimeImmutable $start_date): static
    {
        $this->start_date = $start_date;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->end_date;
    }

    public function setEndDate(\DateTimeImmutable $end_date): static
    {
        $this->end_date = $end_date;

        return $this;
    }

    public function getAssignedAt(): ?\DateTimeImmutable
    {
        return $this->assigned_at;
    }

    public function setAssignedAt(\DateTimeImmutable $assigned_at): static
    {
        $this->assigned_at = $assigned_at;

        return $this;
    }

    public function getRespondedAt(): ?\DateTimeImmutable
    {
        return $this->responded_at;
    }

    public function setRespondedAt(\DateTimeImmutable $responded_at): static
    {
        $this->responded_at = $responded_at;

        return $this;
    }
}
