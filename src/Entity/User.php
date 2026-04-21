<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:ref'])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Groups(['user:ref'])]
    private ?string $username = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:ref'])]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:ref'])]
    private ?string $displayname = null;

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at;

    #[ORM\Column]
    #[Groups(['user:ref'])]
    private ?bool $is_minimal_mode = false;

    /**
     * @var Collection<int, Plants>
     */
    #[ORM\OneToMany(targetEntity: Plants::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $plants;

    /**
     * @var Collection<int, Locations>
     */
    #[ORM\OneToMany(targetEntity: Locations::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $locations;

    /**
     * @var Collection<int, TaskAssignments>
     */
    #[ORM\OneToMany(targetEntity: TaskAssignments::class, mappedBy: 'from_user', orphanRemoval: true)]
    private Collection $send_task_assignemnts;

    /**
     * @var Collection<int, TaskAssignments>
     */
    #[ORM\OneToMany(targetEntity: TaskAssignments::class, mappedBy: 'to_user', orphanRemoval: true)]
    private Collection $received_task_assignments;

    /**
     * @var Collection<int, Notifications>
     */
    #[ORM\OneToMany(targetEntity: Notifications::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $notifications;

    /**
     * @var Collection<int, CareHistory>
     */
    #[ORM\OneToMany(targetEntity: CareHistory::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $careHistories;

    /**
     * @var Collection<int, WishlistPlants>
     */
    #[ORM\OneToMany(targetEntity: WishlistPlants::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $wishlistPlants;

    public function __construct()
    {
        $this->created_at = new \DateTimeImmutable();
        $this->plants = new ArrayCollection();
        $this->locations = new ArrayCollection();
        $this->send_task_assignemnts = new ArrayCollection();
        $this->received_task_assignments = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->careHistories = new ArrayCollection();
        $this->wishlistPlants = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getDisplayname(): ?string
    {
        return $this->displayname;
    }

    public function setDisplayname(?string $displayname): static
    {
        $this->displayname = $displayname;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

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

    public function isMinimalMode(): ?bool
    {
        return $this->is_minimal_mode;
    }

    public function setIsMinimalMode(bool $is_minimal_mode): static
    {
        $this->is_minimal_mode = $is_minimal_mode;

        return $this;
    }

    /**
     * @return Collection<int, Plants>
     */
    public function getPlants(): Collection
    {
        return $this->plants;
    }

    public function addPlant(Plants $plant): static
    {
        if (!$this->plants->contains($plant)) {
            $this->plants->add($plant);
            $plant->setUser($this);
        }

        return $this;
    }

    public function removePlant(Plants $plant): static
    {
        if ($this->plants->removeElement($plant)) {
            // set the owning side to null (unless already changed)
            if ($plant->getUser() === $this) {
                $plant->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Locations>
     */
    public function getLocations(): Collection
    {
        return $this->locations;
    }

    public function addLocation(Locations $location): static
    {
        if (!$this->locations->contains($location)) {
            $this->locations->add($location);
            $location->setUser($this);
        }

        return $this;
    }

    public function removeLocation(Locations $location): static
    {
        if ($this->locations->removeElement($location)) {
            // set the owning side to null (unless already changed)
            if ($location->getUser() === $this) {
                $location->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TaskAssignments>
     */
    public function getSendTaskAssignemnts(): Collection
    {
        return $this->send_task_assignemnts;
    }

    public function addSendTaskAssignemnt(TaskAssignments $sendTaskAssignemnt): static
    {
        if (!$this->send_task_assignemnts->contains($sendTaskAssignemnt)) {
            $this->send_task_assignemnts->add($sendTaskAssignemnt);
            $sendTaskAssignemnt->setFromUser($this);
        }

        return $this;
    }

    public function removeSendTaskAssignemnt(TaskAssignments $sendTaskAssignemnt): static
    {
        if ($this->send_task_assignemnts->removeElement($sendTaskAssignemnt)) {
            // set the owning side to null (unless already changed)
            if ($sendTaskAssignemnt->getFromUser() === $this) {
                $sendTaskAssignemnt->setFromUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TaskAssignments>
     */
    public function getReceivedTaskAssignments(): Collection
    {
        return $this->received_task_assignments;
    }

    public function addReceivedTaskAssignment(TaskAssignments $receivedTaskAssignment): static
    {
        if (!$this->received_task_assignments->contains($receivedTaskAssignment)) {
            $this->received_task_assignments->add($receivedTaskAssignment);
            $receivedTaskAssignment->setToUser($this);
        }

        return $this;
    }

    public function removeReceivedTaskAssignment(TaskAssignments $receivedTaskAssignment): static
    {
        if ($this->received_task_assignments->removeElement($receivedTaskAssignment)) {
            // set the owning side to null (unless already changed)
            if ($receivedTaskAssignment->getToUser() === $this) {
                $receivedTaskAssignment->setToUser(null);
            }
        }

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
            $notification->setUser($this);
        }

        return $this;
    }

    public function removeNotification(Notifications $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getUser() === $this) {
                $notification->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CareHistory>
     */
    public function getCareHistories(): Collection
    {
        return $this->careHistories;
    }

    public function addCareHistory(CareHistory $careHistory): static
    {
        if (!$this->careHistories->contains($careHistory)) {
            $this->careHistories->add($careHistory);
            $careHistory->setUser($this);
        }

        return $this;
    }

    public function removeCareHistory(CareHistory $careHistory): static
    {
        if ($this->careHistories->removeElement($careHistory)) {
            // set the owning side to null (unless already changed)
            if ($careHistory->getUser() === $this) {
                $careHistory->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, WishlistPlants>
     */
    public function getWishlistPlants(): Collection
    {
        return $this->wishlistPlants;
    }

    public function addWishlistPlant(WishlistPlants $wishlistPlant): static
    {
        if (!$this->wishlistPlants->contains($wishlistPlant)) {
            $this->wishlistPlants->add($wishlistPlant);
            $wishlistPlant->setUser($this);
        }

        return $this;
    }

    public function removeWishlistPlant(WishlistPlants $wishlistPlant): static
    {
        if ($this->wishlistPlants->removeElement($wishlistPlant)) {
            // set the owning side to null (unless already changed)
            if ($wishlistPlant->getUser() === $this) {
                $wishlistPlant->setUser(null);
            }
        }

        return $this;
    }
}
