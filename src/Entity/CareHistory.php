<?php

namespace App\Entity;

use App\Enum\CareType;
use App\Repository\CareHistoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CareHistoryRepository::class)]
class CareHistory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: CareType::class)]
    private ?CareType $care_type = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $performed_at;

    #[ORM\Column(nullable: true)]
    private ?int $water_amount_ml = null;

    #[ORM\Column(nullable: true)]
    private ?int $fertilizer_amount_ml = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    #[ORM\ManyToOne(inversedBy: 'care_history')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Plants $plant = null;

    #[ORM\ManyToOne(inversedBy: 'careHistories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function __construct()
    {
        $this->performed_at = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCareType(): ?CareType
    {
        return $this->care_type;
    }

    public function setCareType(CareType $care_type): static
    {
        $this->care_type = $care_type;

        return $this;
    }

    public function getPerformedAt(): ?\DateTimeImmutable
    {
        return $this->performed_at;
    }

    public function setPerformedAt(\DateTimeImmutable $performed_at): static
    {
        $this->performed_at = $performed_at;

        return $this;
    }

    public function getWaterAmountMl(): ?int
    {
        return $this->water_amount_ml;
    }

    public function setWaterAmountMl(?int $water_amount_ml): static
    {
        $this->water_amount_ml = $water_amount_ml;

        return $this;
    }

    public function getFertilizerAmountMl(): ?int
    {
        return $this->fertilizer_amount_ml;
    }

    public function setFertilizerAmountMl(?int $fertilizer_amount_ml): static
    {
        $this->fertilizer_amount_ml = $fertilizer_amount_ml;

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

    public function getPlant(): ?Plants
    {
        return $this->plant;
    }

    public function setPlant(?Plants $plant): static
    {
        $this->plant = $plant;

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
