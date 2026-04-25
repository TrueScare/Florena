<?php

namespace App\Entity;

use App\Enum\PropagationMethod;
use App\Enum\Status;
use App\Repository\PropagationActionsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    private ?\DateTimeImmutable $created_at;

    #[ORM\ManyToOne(inversedBy: 'propagation_actions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Plants $plant = null;

    /**
     * @var Collection<int, Notifications>
     */
    #[ORM\OneToMany(targetEntity: Notifications::class, mappedBy: 'propagation_action')]
    private Collection $notifications;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
        $this->notifications = new ArrayCollection();
        $this->planned_date = new \DateTimeImmutable();

        $this->status ??= Status::planned;
    }

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
            $notification->setPropagationAction($this);
        }

        return $this;
    }

    public function removeNotification(Notifications $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getPropagationAction() === $this) {
                $notification->setPropagationAction(null);
            }
        }

        return $this;
    }
}
