<?php

namespace App\Entity;

use App\Enum\AdminPermission;
use App\Enum\AdminRole;
use App\Enum\AdminStatus;
use App\Repository\AdminUserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AdminUserRepository::class)]
#[ORM\Table(name: 'admin_user')]
#[ORM\HasLifecycleCallbacks]
class AdminUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private string $fullName;

    // Identifiant de connexion (distinct de l'email, peut être un simple login court)
    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank]
    private string $login;

    #[ORM\Column(length: 180, nullable: true)]
    #[Assert\Email]
    private ?string $email = null;

    // PIN hashé (jamais en clair)
    #[ORM\Column(length: 255)]
    private string $pin;

    #[ORM\Column(length: 20, enumType: AdminRole::class)]
    private AdminRole $role;

    // Permissions effectives de cet admin : initialisées depuis les valeurs par défaut
    // du rôle à la création, mais modifiables individuellement ensuite.
    #[ORM\Column(type: Types::JSON)]
    private array $permissions = [];

    #[ORM\Column(length: 20, enumType: AdminStatus::class)]
    private AdminStatus $status;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $lastLoginAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->status = AdminStatus::Active;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function setLogin(string $login): static
    {
        $this->login = $login;

        return $this;
    }

    public function getRole(): AdminRole
    {
        return $this->role;
    }

    public function setRole(AdminRole $role): static
    {
        $this->role = $role;

        return $this;
    }

    /** @return AdminPermission[] */
    public function getPermissions(): array
    {
        return array_map(
            fn(string $value) => AdminPermission::from($value),
            $this->permissions,
        );
    }

    /** @param AdminPermission[] $permissions */
    public function setPermissions(array $permissions): static
    {
        $this->permissions = array_map(fn(AdminPermission $p) => $p->value, $permissions);

        return $this;
    }

    public function hasPermission(AdminPermission $permission): bool
    {
        return \in_array($permission->value, $this->permissions, true);
    }

    public function getStatus(): AdminStatus
    {
        return $this->status;
    }

    public function setStatus(AdminStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getLastLoginAt(): ?\DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    public function recordLogin(): static
    {
        $this->lastLoginAt = new \DateTimeImmutable();

        return $this;
    }

    // --- UserInterface / PasswordAuthenticatedUserInterface ---

    public function getUserIdentifier(): string
    {
        return $this->login;
    }

    public function getRoles(): array
    {
        // ROLE_ADMIN sert de porte d'entrée au firewall admin ; le rôle fonctionnel
        // précis (super_admin, superviseur...) est disponible via getRole(), et les
        // vérifications fines passent par hasPermission() dans un Voter dédié.
        $roles = ['ROLE_ADMIN'];
        $roles[] = 'ROLE_' . strtoupper($this->role->value);

        return array_unique($roles);
    }

    public function getPassword(): ?string
    {
        return $this->pin;
    }

    public function setPin(string $hashedPin): static
    {
        $this->pin = $hashedPin;

        return $this;
    }

    public function eraseCredentials(): void
    {
        // rien de sensible en clair à effacer, le PIN n'est jamais stocké en clair
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPin(): ?string
    {
        return $this->pin;
    }

    public function setLastLoginAt(?\DateTimeImmutable $lastLoginAt): static
    {
        $this->lastLoginAt = $lastLoginAt;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
