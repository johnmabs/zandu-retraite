<?php

namespace App\Entity\Embeddable;

use Doctrine\ORM\Mapping as ORM;

// Localisation du lieu d'activité commerciale (marché, etc.)
#[ORM\Embeddable]
class ActivityLocation
{
    #[ORM\Column(length: 100, nullable: true)]
    public ?string $department = null;

    #[ORM\Column(length: 100, nullable: true)]
    public ?string $commune = null;

    #[ORM\Column(length: 100, nullable: true)]
    public ?string $quarter = null;

    // Ex: "Marché Total", "Grand Marché"
    #[ORM\Column(length: 150, nullable: true)]
    public ?string $marketZone = null;

    // Ex: "Allée B, Stand 12"
    #[ORM\Column(length: 150, nullable: true)]
    public ?string $marketSpot = null;

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function setDepartment(?string $department): static
    {
        $this->department = $department;

        return $this;
    }

    public function getCommune(): ?string
    {
        return $this->commune;
    }

    public function setCommune(?string $commune): static
    {
        $this->commune = $commune;

        return $this;
    }

    public function getQuarter(): ?string
    {
        return $this->quarter;
    }

    public function setQuarter(?string $quarter): static
    {
        $this->quarter = $quarter;

        return $this;
    }

    public function getMarketZone(): ?string
    {
        return $this->marketZone;
    }

    public function setMarketZone(?string $marketZone): static
    {
        $this->marketZone = $marketZone;

        return $this;
    }

    public function getMarketSpot(): ?string
    {
        return $this->marketSpot;
    }

    public function setMarketSpot(?string $marketSpot): static
    {
        $this->marketSpot = $marketSpot;

        return $this;
    }
}
