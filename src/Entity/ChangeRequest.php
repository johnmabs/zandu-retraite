<?php

namespace App\Entity;

use App\Enum\ChangeRequestStatus;
use App\Enum\ChangeRequestType;
use App\Repository\ChangeRequestRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ChangeRequestRepository::class)]
#[ORM\Table(name: 'change_request')]
class ChangeRequest
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Member $member;

    #[ORM\Column(length: 30, enumType: ChangeRequestType::class)]
    private ChangeRequestType $type;

    // Valeur demandée (ex: nouveau numéro de téléphone, id du nouveau secteur...)
    #[ORM\Column(length: 255)]
    private string $requestedValue;

    #[ORM\Column(length: 20, enumType: ChangeRequestStatus::class)]
    private ChangeRequestStatus $status;

    #[ORM\ManyToOne(targetEntity: AdminUser::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?AdminUser $reviewedBy = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $reviewNote = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $reviewedAt = null;

    public function __construct()
    {
        $this->status = ChangeRequestStatus::Pending;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getType(): ?ChangeRequestType
    {
        return $this->type;
    }

    public function setType(ChangeRequestType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getRequestedValue(): ?string
    {
        return $this->requestedValue;
    }

    public function setRequestedValue(string $requestedValue): static
    {
        $this->requestedValue = $requestedValue;

        return $this;
    }

    public function getStatus(): ?ChangeRequestStatus
    {
        return $this->status;
    }

    public function setStatus(ChangeRequestStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getReviewNote(): ?string
    {
        return $this->reviewNote;
    }

    public function setReviewNote(?string $reviewNote): static
    {
        $this->reviewNote = $reviewNote;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getReviewedAt(): ?\DateTimeImmutable
    {
        return $this->reviewedAt;
    }

    public function setReviewedAt(?\DateTimeImmutable $reviewedAt): static
    {
        $this->reviewedAt = $reviewedAt;

        return $this;
    }

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function setMember(?Member $member): static
    {
        $this->member = $member;

        return $this;
    }

    public function getReviewedBy(): ?AdminUser
    {
        return $this->reviewedBy;
    }

    public function setReviewedBy(?AdminUser $reviewedBy): static
    {
        $this->reviewedBy = $reviewedBy;

        return $this;
    }
}
