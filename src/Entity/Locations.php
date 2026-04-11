<?php

namespace App\Entity;

use App\Enum\HumidityRequirement;
use App\Enum\LightRequirement;
use App\Enum\TemperatureRequirement;
use App\Repository\LocationsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LocationsRepository::class)]
class Locations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(enumType: LightRequirement::class)]
    private ?LightRequirement $light_condition = null;

    #[ORM\Column(enumType: TemperatureRequirement::class)]
    private ?TemperatureRequirement $temperature_level = null;

    #[ORM\Column(enumType: HumidityRequirement::class)]
    private ?HumidityRequirement $humidity_level = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at;

    public function __construct(){
        $this->created_at = new \DateTimeImmutable();
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

    public function getTemperatureLevel(): ?TemperatureRequirement
    {
        return $this->temperature_level;
    }

    public function setTemperatureLevel(TemperatureRequirement $temperature_level): static
    {
        $this->temperature_level = $temperature_level;

        return $this;
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
