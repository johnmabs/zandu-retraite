<?php

namespace App\Entity;

use App\Enum\AuditEventType;
use App\Repository\AuditLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

// Journal d'audit de sécurité : IMMUABLE, jamais modifié ni supprimé après écriture
#[ORM\Entity(repositoryClass: AuditLogRepository::class)]
#[ORM\Table(name: 'audit_log')]
#[ORM\Index(columns: ['event_type', 'created_at'])]
class AuditLog
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(length: 40, enumType: AuditEventType::class)]
    private AuditEventType $eventType;

    #[ORM\Column(type: Types::TEXT)]
    private string $description;

    // Admin ayant réalisé l'action (nul si action système ou membre)
    #[ORM\ManyToOne(targetEntity: AdminUser::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?AdminUser $actorAdmin = null;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Member $actorMember = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $ipAddress = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $context = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getEventType(): ?AuditEventType
    {
        return $this->eventType;
    }

    public function setEventType(AuditEventType $eventType): static
    {
        $this->eventType = $eventType;

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

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    public function getContext(): ?array
    {
        return $this->context;
    }

    public function setContext(?array $context): static
    {
        $this->context = $context;

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

    public function getActorAdmin(): ?AdminUser
    {
        return $this->actorAdmin;
    }

    public function setActorAdmin(?AdminUser $actorAdmin): static
    {
        $this->actorAdmin = $actorAdmin;

        return $this;
    }

    public function getActorMember(): ?Member
    {
        return $this->actorMember;
    }

    public function setActorMember(?Member $actorMember): static
    {
        $this->actorMember = $actorMember;

        return $this;
    }
}
