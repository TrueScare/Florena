<?php

namespace App\Entity;

use App\Repository\TaskAssignmentsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: TaskAssignmentsRepository::class)]
#[ORM\UniqueConstraint(name: 'task_assignment_user', columns: ['to_user_id', 'care_task_id'])]
#[UniqueEntity(fields: ['to_user', 'care_task'], message: "Dem User wurde diese Aufgabe bereits zugeordnet.")]
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

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $responded_at = null;

    #[ORM\ManyToOne(inversedBy: 'send_task_assignemnts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $from_user = null;

    #[ORM\ManyToOne(inversedBy: 'received_task_assignments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $to_user = null;

    #[ORM\ManyToOne(inversedBy: 'taskAssignments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CareTask $care_task = null;

    public function __construct()
    {
        $this->assigned_at = new \DateTimeImmutable();
    }

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

    public function getFromUser(): ?User
    {
        return $this->from_user;
    }

    public function setFromUser(?User $from_user): static
    {
        $this->from_user = $from_user;

        return $this;
    }

    public function getToUser(): ?User
    {
        return $this->to_user;
    }

    public function setToUser(?User $to_user): static
    {
        $this->to_user = $to_user;

        return $this;
    }

    public function getCareTask(): ?CareTask
    {
        return $this->care_task;
    }

    public function setCareTask(?CareTask $care_task): static
    {
        $this->care_task = $care_task;

        return $this;
    }
}
