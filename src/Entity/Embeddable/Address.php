<?php

namespace App\Entity\Embeddable;

use Doctrine\ORM\Mapping as ORM;

// Adresse générique réutilisée pour le domicile
#[ORM\Embeddable]
class Address
{
    #[ORM\Column(length: 100, nullable: true)]
    public ?string $department = null;

    #[ORM\Column(length: 100, nullable: true)]
    public ?string $commune = null;

    #[ORM\Column(length: 100, nullable: true)]
    public ?string $quarter = null;

    #[ORM\Column(length: 150, nullable: true)]
    public ?string $street = null;

    #[ORM\Column(length: 20, nullable: true)]
    public ?string $number = null;

    #[ORM\Column(length: 150, nullable: true)]
    public ?string $locality = null;

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

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): static
    {
        $this->street = $street;

        return $this;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(?string $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getLocality(): ?string
    {
        return $this->locality;
    }

    public function setLocality(?string $locality): static
    {
        $this->locality = $locality;

        return $this;
    }
}
