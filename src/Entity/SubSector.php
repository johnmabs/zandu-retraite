<?php

namespace App\Entity;

use App\Repository\SubSectorRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SubSectorRepository::class)]
#[ORM\Table(name: 'sub_sector')]
class SubSector
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(targetEntity: Sector::class, inversedBy: 'subSectors')]
    #[ORM\JoinColumn(nullable: false)]
    private Sector $sector;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private string $name;

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getSector(): Sector
    {
        return $this->sector;
    }

    public function setSector(Sector $sector): static
    {
        $this->sector = $sector;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }
}
