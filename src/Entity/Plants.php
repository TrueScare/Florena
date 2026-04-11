<?php

namespace App\Entity;

use App\Enum\HumidityRequirement;
use App\Enum\LightRequirement;
use App\Enum\TemperatureRequirement;
use App\Repository\PlantsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlantsRepository::class)]
#[ORM\UniqueConstraint(name: 'user_plant_name', columns: ['user_id', 'name'])]
class Plants
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $botanical_name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_path = null;

    #[ORM\Column(enumType: LightRequirement::class)]
    private ?LightRequirement $light_requirement = null;

    #[ORM\Column(enumType: TemperatureRequirement::class)]
    private ?TemperatureRequirement $temperature_requirement = null;

    #[ORM\Column(enumType: HumidityRequirement::class)]
    private ?HumidityRequirement $humidity_requirement = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $soil_type = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $pot_size = null;

    #[ORM\Column(nullable: true)]
    private ?int $watering_interval_days = null;

    #[ORM\Column(nullable: true)]
    private ?int $fertilizing_interval_days = null;

    #[ORM\Column(nullable: true)]
    private ?int $repotting_interval_days = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $last_watered_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $last_fertilized_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $last_repotted_at = null;

    #[ORM\Column]
    private ?bool $toxic_for_humans = null;

    #[ORM\Column]
    private ?bool $toxic_for_animals = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $purchase_date = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at;

    #[ORM\Column]
    private ?int $stress_score = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $died_at = null;

    public function __construct()
    {
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

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getBotanicalName(): ?string
    {
        return $this->botanical_name;
    }

    public function setBotanicalName(string $botanical_name): static
    {
        $this->botanical_name = $botanical_name;

        return $this;
    }

    public function getPhotoPath(): ?string
    {
        return $this->photo_path;
    }

    public function setPhotoPath(string $photo_path): static
    {
        $this->photo_path = $photo_path;

        return $this;
    }

    public function getLightRequirement(): ?LightRequirement
    {
        return $this->light_requirement;
    }

    public function setLightRequirement(LightRequirement $light_requirement): static
    {
        $this->light_requirement = $light_requirement;

        return $this;
    }

    public function getTemperatureRequirement(): ?TemperatureRequirement
    {
        return $this->temperature_requirement;
    }

    public function setTemperatureRequirement(TemperatureRequirement $temperature_requirement): static
    {
        $this->temperature_requirement = $temperature_requirement;

        return $this;
    }

    public function getHumidityRequirement(): ?HumidityRequirement
    {
        return $this->humidity_requirement;
    }

    public function setHumidityRequirement(HumidityRequirement $humidity_requirement): static
    {
        $this->humidity_requirement = $humidity_requirement;

        return $this;
    }

    public function getSoilType(): ?string
    {
        return $this->soil_type;
    }

    public function setSoilType(string $soil_type): static
    {
        $this->soil_type = $soil_type;

        return $this;
    }

    public function getPotSize(): ?string
    {
        return $this->pot_size;
    }

    public function setPotSize(string $pot_size): static
    {
        $this->pot_size = $pot_size;

        return $this;
    }

    public function getWateringIntervalDays(): ?int
    {
        return $this->watering_interval_days;
    }

    public function setWateringIntervalDays(int $watering_interval_days): static
    {
        $this->watering_interval_days = $watering_interval_days;

        return $this;
    }

    public function getFertilizingIntervalDays(): ?int
    {
        return $this->fertilizing_interval_days;
    }

    public function setFertilizingIntervalDays(?int $fertilizing_interval_days): static
    {
        $this->fertilizing_interval_days = $fertilizing_interval_days;

        return $this;
    }

    public function getRepottingIntervalDays(): ?int
    {
        return $this->repotting_interval_days;
    }

    public function setRepottingIntervalDays(?int $repotting_interval_days): static
    {
        $this->repotting_interval_days = $repotting_interval_days;

        return $this;
    }

    public function getLastWateredAt(): ?\DateTimeImmutable
    {
        return $this->last_watered_at;
    }

    public function setLastWateredAt(\DateTimeImmutable $last_watered_at): static
    {
        $this->last_watered_at = $last_watered_at;

        return $this;
    }

    public function getLastFertilizedAt(): ?\DateTimeImmutable
    {
        return $this->last_fertilized_at;
    }

    public function setLastFertilizedAt(\DateTimeImmutable $last_fertilized_at): static
    {
        $this->last_fertilized_at = $last_fertilized_at;

        return $this;
    }

    public function getLastRepottedAt(): ?\DateTimeImmutable
    {
        return $this->last_repotted_at;
    }

    public function setLastRepottedAt(\DateTimeImmutable $last_repotted_at): static
    {
        $this->last_repotted_at = $last_repotted_at;

        return $this;
    }

    public function isToxicForHumans(): ?bool
    {
        return $this->toxic_for_humans;
    }

    public function setToxicForHumans(bool $toxic_for_humans): static
    {
        $this->toxic_for_humans = $toxic_for_humans;

        return $this;
    }

    public function isToxicForAnimals(): ?bool
    {
        return $this->toxic_for_animals;
    }

    public function setToxicForAnimals(bool $toxic_for_animals): static
    {
        $this->toxic_for_animals = $toxic_for_animals;

        return $this;
    }

    public function getPurchaseDate(): ?\DateTimeImmutable
    {
        return $this->purchase_date;
    }

    public function setPurchaseDate(?\DateTimeImmutable $purchase_date): static
    {
        $this->purchase_date = $purchase_date;

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

    public function getStressScore(): ?int
    {
        return $this->stress_score;
    }

    public function setStressScore(int $stress_score): static
    {
        $this->stress_score = $stress_score;

        return $this;
    }

    public function getDiedAt(): ?\DateTimeImmutable
    {
        return $this->died_at;
    }

    public function setDiedAt(?\DateTimeImmutable $died_at): static
    {
        $this->died_at = $died_at;

        return $this;
    }
}
