<?php

namespace App\Entity;

use App\Repository\SectorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SectorRepository::class)]
#[ORM\Table(name: 'sector')]
class Sector
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(length: 100, unique: true)]
    #[Assert\NotBlank]
    private string $name;

    #[ORM\Column(length: 20, unique: true)]
    #[Assert\NotBlank]
    private string $code;

    #[ORM\OneToMany(mappedBy: 'sector', targetEntity: SubSector::class, orphanRemoval: true)]
    private Collection $subSectors;

    #[ORM\Column]
    private bool $isOther = false;

    public function __construct()
    {
        $this->subSectors = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
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

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    /** @return Collection<int, SubSector> */
    public function getSubSectors(): Collection
    {
        return $this->subSectors;
    }

    public function addSubSector(SubSector $subSector): static
    {
        if (!$this->subSectors->contains($subSector)) {
            $this->subSectors->add($subSector);
            $subSector->setSector($this);
        }

        return $this;
    }

    public function removeSubSector(SubSector $subSector): static
    {
        if ($this->subSectors->removeElement($subSector)) {
            // set the owning side to null (unless already changed)
            if ($subSector->getSector() === $this) {
                $subSector->setSector(null);
            }
        }

        return $this;
    }

    public function isOther(): bool
    {
        return $this->isOther;
    }

    public function setIsOther(bool $isOther): static
    {
        $this->isOther = $isOther;

        return $this;
    }
}
