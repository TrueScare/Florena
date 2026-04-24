<?php

namespace App\Entity;

use App\Enum\HumidityRequirement;
use App\Enum\LightRequirement;
use App\Enum\TemperatureRequirement;
use App\Repository\LocationsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: LocationsRepository::class)]
#[ORM\UniqueConstraint(name: 'user_location_name', columns: ['user_id', 'name'])]
class Locations implements RequirementsEntityInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('location:ref')]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups('location:ref')]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups('location:ref')]
    private ?string $description = null;

    #[ORM\Column(enumType: LightRequirement::class)]
    #[Groups('location:ref')]
    private ?LightRequirement $light_condition = null;

    #[ORM\Column(enumType: TemperatureRequirement::class)]
    #[Groups('location:ref')]
    private ?TemperatureRequirement $temperature_level = null;

    #[ORM\Column(enumType: HumidityRequirement::class)]
    #[Groups('location:ref')]
    private ?HumidityRequirement $humidity_level = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at;

    /**
     * @var Collection<int, Plants>
     */
    #[ORM\OneToMany(targetEntity: Plants::class, mappedBy: 'location')]
    private Collection $plants;

    /**
     * @var Collection<int, WishlistPlants>
     */
    #[ORM\OneToMany(targetEntity: WishlistPlants::class, mappedBy: 'location')]
    private Collection $wishlist_plants;

    #[ORM\ManyToOne(inversedBy: 'locations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function __construct(){
        $this->created_at = new \DateTimeImmutable();
        $this->plants = new ArrayCollection();
        $this->wishlist_plants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getLightCondition(): ?LightRequirement
    {
        return $this->light_condition;
    }

    public function setLightCondition(LightRequirement $light_condition): static
    {
        $this->light_condition = $light_condition;

        return $this;
    }

    public function getLightColor(): string
    {
        return match ($this->light_condition?->value) {
            'sonnig' => 'bg-yellow-100 text-yellow-700',
            'hell' => 'bg-yellow-50 text-yellow-600',
            'halbschattig' => 'bg-green-100 text-green-700',
            'schattig' => 'bg-gray-100 text-gray-600',
            default => 'bg-gray-100 text-gray-600',
        };
    }

    public function getTemperatureLevel(): ?TemperatureRequirement
    {
        return $this->temperature_level;
    }

    public function setTemperatureLevel(TemperatureRequirement $temperature_level): static
    {
        $this->temperature_level = $temperature_level;

        return $this;
    }

    public function getTemperatureColor(): string
    {
        $value = $this->temperature_level?->value;

        if (!$value) return 'bg-gray-100 text-gray-600';

        if (str_contains($value, 'kühl')) {
            return 'bg-blue-100 text-blue-700';
        }

        if (str_contains($value, 'warm')) {
            return 'bg-orange-100 text-orange-700';
        }

        return 'bg-gray-100 text-gray-600';
    }

    public function getHumidityLevel(): ?HumidityRequirement
    {
        return $this->humidity_level;
    }

    public function setHumidityLevel(HumidityRequirement $humidity_level): static
    {
        $this->humidity_level = $humidity_level;

        return $this;
    }

    public function getHumidityColor(): string
    {
        return match ($this->humidity_level?->value) {
            'hoch' => 'bg-blue-200 text-blue-800',
            'mittel' => 'bg-blue-100 text-blue-700',
            'niedrig' => 'bg-gray-100 text-gray-600',
            default => 'bg-gray-100 text-gray-600',
        };
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
            $plant->setLocation($this);
        }

        return $this;
    }

    public function removePlant(Plants $plant): static
    {
        if ($this->plants->removeElement($plant)) {
            // set the owning side to null (unless already changed)
            if ($plant->getLocation() === $this) {
                $plant->setLocation(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, WishlistPlants>
     */
    public function getWishlistPlants(): Collection
    {
        return $this->wishlist_plants;
    }

    public function addWishlistPlant(WishlistPlants $wishlistPlant): static
    {
        if (!$this->wishlist_plants->contains($wishlistPlant)) {
            $this->wishlist_plants->add($wishlistPlant);
            $wishlistPlant->setLocation($this);
        }

        return $this;
    }

    public function removeWishlistPlant(WishlistPlants $wishlistPlant): static
    {
        if ($this->wishlist_plants->removeElement($wishlistPlant)) {
            // set the owning side to null (unless already changed)
            if ($wishlistPlant->getLocation() === $this) {
                $wishlistPlant->setLocation(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
