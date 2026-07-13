<?php

namespace App\Entity\Embeddable;

use Doctrine\ORM\Mapping as ORM;

// Bénéficiaire désigné (versement en cas de décès de l'adhérent)
#[ORM\Embeddable]
class Beneficiary
{
    #[ORM\Column(length: 150, nullable: true)]
    public ?string $name = null;

    #[ORM\Column(length: 20, nullable: true)]
    public ?string $phone = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }
}
