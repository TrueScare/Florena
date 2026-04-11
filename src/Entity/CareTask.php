<?php

namespace App\Entity;

use App\Enum\CareType;
use App\Repository\CareTaskRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CareTaskRepository::class)]
class CareTask
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: CareType::class)]
    private ?CareType $task_type = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $due_date = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at;

    #[ORM\ManyToOne(inversedBy: 'care_tasks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Plants $plant = null;

    /**
     * @var Collection<int, Notifications>
     */
    #[ORM\OneToMany(targetEntity: Notifications::class, mappedBy: 'care_task')]
    private Collection $notifications;

    /**
     * @var Collection<int, TaskAssignments>
     */
    #[ORM\OneToMany(targetEntity: TaskAssignments::class, mappedBy: 'care_task', orphanRemoval: true)]
    private Collection $taskAssignments;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
        $this->notifications = new ArrayCollection();
        $this->taskAssignments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTaskType(): ?CareType
    {
        return $this->task_type;
    }

    public function setTaskType(CareType $task_type): static
    {
        $this->task_type = $task_type;

        return $this;
    }

    public function getDueDate(): ?\DateTimeImmutable
    {
        return $this->due_date;
    }

    public function setDueDate(\DateTimeImmutable $due_date): static
    {
        $this->due_date = $due_date;

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

    public function getPlant(): ?Plants
    {
        return $this->plant;
    }

    public function setPlant(?Plants $plant): static
    {
        $this->plant = $plant;

        return $this;
    }

    /**
     * @return Collection<int, Notifications>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notifications $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setCareTask($this);
        }

        return $this;
    }

    public function removeNotification(Notifications $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getCareTask() === $this) {
                $notification->setCareTask(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TaskAssignments>
     */
    public function getTaskAssignments(): Collection
    {
        return $this->taskAssignments;
    }

    public function addTaskAssignment(TaskAssignments $taskAssignment): static
    {
        if (!$this->taskAssignments->contains($taskAssignment)) {
            $this->taskAssignments->add($taskAssignment);
            $taskAssignment->setCareTask($this);
        }

        return $this;
    }

    public function removeTaskAssignment(TaskAssignments $taskAssignment): static
    {
        if ($this->taskAssignments->removeElement($taskAssignment)) {
            // set the owning side to null (unless already changed)
            if ($taskAssignment->getCareTask() === $this) {
                $taskAssignment->setCareTask(null);
            }
        }

        return $this;
    }
}
