<?php

namespace App\Entity;

use App\Entity\Embeddable\ActivityLocation;
use App\Entity\Embeddable\Address;
use App\Entity\Embeddable\Beneficiary;
use App\Enum\EngagementDuration;
use App\Enum\Gender;
use App\Enum\MemberStatus;
use App\Enum\SalaryCategory;
use App\Enum\SavingsGoal;
use App\Repository\MemberRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: MemberRepository::class)]
#[ORM\Table(name: 'member')]
#[UniqueEntity(fields: ['phone'], message: 'Ce numéro de téléphone est déjà utilisé par un autre membre.')]
#[ORM\HasLifecycleCallbacks]
class Member implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    // Numéro fonctionnel lisible affiché à l'utilisateur (ex: MR-0001)
    #[ORM\Column(length: 20, unique: true)]
    private string $memberNumber;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private string $firstName;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private string $lastName;

    #[ORM\Column(length: 10, enumType: Gender::class, nullable: true)]
    private ?Gender $gender = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $birthDate = null;

    // Identifiant de connexion
    #[ORM\Column(length: 20, unique: true)]
    #[Assert\NotBlank]
    private string $phone;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $whatsappPhone = null;

    #[ORM\Column(length: 180, nullable: true)]
    #[Assert\Email]
    private ?string $email = null;

    // Numéro de pièce d'identité (CNI, passeport...)
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $idDocumentNumber = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $profession = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $customSectorLabel = null;

    #[ORM\ManyToOne(targetEntity: Sector::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Sector $sector;

    #[ORM\ManyToOne(targetEntity: SubSector::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?SubSector $subSector = null;

    #[ORM\Embedded(class: ActivityLocation::class)]
    private ActivityLocation $activityLocation;

    #[ORM\Embedded(class: Address::class)]
    private Address $homeAddress;

    #[ORM\Embedded(class: Beneficiary::class)]
    private Beneficiary $beneficiary;

    // PIN à 4 chiffres, toujours stocké hashé (jamais en clair)
    #[ORM\Column(length: 255)]
    private string $pin;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoPath = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    #[Assert\GreaterThanOrEqual(500)]
    private string $dailyPaymentAmount;

    #[ORM\Column(enumType: SalaryCategory::class)]
    private SalaryCategory $salaryCategory;

    #[ORM\Column(enumType: EngagementDuration::class, nullable: true)]
    private ?EngagementDuration $engagementDuration = null;

    #[ORM\Column(enumType: SavingsGoal::class, nullable: true)]
    private ?SavingsGoal $savingsGoal = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $goalDetails = null; // si savingsGoal === Other

    // Taux personnalisés (nullable = utilise les valeurs par défaut de Setting)
    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $pensionRate = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $managementFeeRate = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $cnssRate = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    private ?string $registrationFeeAmount = null;


    #[ORM\Column(length: 20, enumType: MemberStatus::class)]
    private MemberStatus $status;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $lastLoginAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private \DateTimeImmutable $registeredAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->status = MemberStatus::Pending;
        $this->salaryCategory = SalaryCategory::Bronze;
        $this->activityLocation = new ActivityLocation();
        $this->homeAddress = new Address();
        $this->beneficiary = new Beneficiary();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMemberNumber(): string
    {
        return $this->memberNumber;
    }

    public function setMemberNumber(string $memberNumber): static
    {
        $this->memberNumber = $memberNumber;

        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getStatus(): MemberStatus
    {
        return $this->status;
    }

    public function setStatus(MemberStatus $status): static
    {
        $this->status = $status;

        return $this;
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

    public function getUserIdentifier(): string
    {
        return $this->phone;
    }

    public function getRoles(): array
    {
        return ['ROLE_MEMBER'];
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
        // le PIN n'est jamais stocké en clair, rien à effacer
    }

    public function getActivityLocation(): ActivityLocation
    {
        return $this->activityLocation;
    }

    public function setActivityLocation(ActivityLocation $activityLocation): static
    {
        $this->activityLocation = $activityLocation;

        return $this;
    }

    public function getHomeAddress(): Address
    {
        return $this->homeAddress;
    }

    public function setHomeAddress(Address $homeAddress): static
    {
        $this->homeAddress = $homeAddress;

        return $this;
    }

    public function getBeneficiary(): Beneficiary
    {
        return $this->beneficiary;
    }

    public function setBeneficiary(Beneficiary $beneficiary): static
    {
        $this->beneficiary = $beneficiary;

        return $this;
    }

    public function getGender(): ?Gender
    {
        return $this->gender;
    }

    public function setGender(?Gender $gender): static
    {
        $this->gender = $gender;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeImmutable
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTimeImmutable $birthDate): static
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getWhatsappPhone(): ?string
    {
        return $this->whatsappPhone;
    }

    public function setWhatsappPhone(?string $whatsappPhone): static
    {
        $this->whatsappPhone = $whatsappPhone;

        return $this;
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

    public function getIdDocumentNumber(): ?string
    {
        return $this->idDocumentNumber;
    }

    public function setIdDocumentNumber(?string $idDocumentNumber): static
    {
        $this->idDocumentNumber = $idDocumentNumber;

        return $this;
    }

    public function getProfession(): ?string
    {
        return $this->profession;
    }

    public function setProfession(?string $profession): static
    {
        $this->profession = $profession;

        return $this;
    }

    public function getCustomSectorLabel(): ?string
    {
        return $this->customSectorLabel;
    }

    public function setCustomSectorLabel(?string $customSectorLabel): static
    {
        $this->customSectorLabel = $customSectorLabel;

        return $this;
    }

    public function getPin(): ?string
    {
        return $this->pin;
    }

    public function getPhotoPath(): ?string
    {
        return $this->photoPath;
    }

    public function setPhotoPath(?string $photoPath): static
    {
        $this->photoPath = $photoPath;

        return $this;
    }

    public function getDailyPaymentAmount(): ?string
    {
        return $this->dailyPaymentAmount;
    }

    public function setDailyPaymentAmount(string $dailyPaymentAmount): static
    {
        $this->dailyPaymentAmount = $dailyPaymentAmount;

        return $this;
    }

    public function getSalaryCategory(): ?SalaryCategory
    {
        return $this->salaryCategory;
    }

    public function setSalaryCategory(SalaryCategory $salaryCategory): static
    {
        $this->salaryCategory = $salaryCategory;

        return $this;
    }

    public function getEngagementDuration(): ?EngagementDuration
    {
        return $this->engagementDuration;
    }

    public function setEngagementDuration(?EngagementDuration $engagementDuration): static
    {
        $this->engagementDuration = $engagementDuration;

        return $this;
    }

    public function getSavingsGoal(): ?SavingsGoal
    {
        return $this->savingsGoal;
    }

    public function setSavingsGoal(?SavingsGoal $savingsGoal): static
    {
        $this->savingsGoal = $savingsGoal;

        return $this;
    }

    public function getGoalDetails(): ?string
    {
        return $this->goalDetails;
    }

    public function setGoalDetails(?string $goalDetails): static
    {
        $this->goalDetails = $goalDetails;

        return $this;
    }

    public function getPensionRate(): ?string
    {
        return $this->pensionRate;
    }

    public function setPensionRate(?string $pensionRate): static
    {
        $this->pensionRate = $pensionRate;

        return $this;
    }

    public function getManagementFeeRate(): ?string
    {
        return $this->managementFeeRate;
    }

    public function setManagementFeeRate(?string $managementFeeRate): static
    {
        $this->managementFeeRate = $managementFeeRate;

        return $this;
    }

    public function getCnssRate(): ?string
    {
        return $this->cnssRate;
    }

    public function setCnssRate(?string $cnssRate): static
    {
        $this->cnssRate = $cnssRate;

        return $this;
    }

    public function getRegistrationFeeAmount(): ?string
    {
        return $this->registrationFeeAmount;
    }

    public function setRegistrationFeeAmount(?string $registrationFeeAmount): static
    {
        $this->registrationFeeAmount = $registrationFeeAmount;

        return $this;
    }

    public function getLastLoginAt(): ?\DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?\DateTimeImmutable $lastLoginAt): static
    {
        $this->lastLoginAt = $lastLoginAt;

        return $this;
    }

    public function getRegisteredAt(): ?\DateTimeImmutable
    {
        return $this->registeredAt;
    }

    public function setRegisteredAt(?\DateTimeImmutable $registeredAt): static
    {
        $this->registeredAt = $registeredAt;

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

    public function getSubSector(): ?SubSector
    {
        return $this->subSector;
    }

    public function setSubSector(?SubSector $subSector): static
    {
        $this->subSector = $subSector;

        return $this;
    }

    #[Assert\Callback]
    public function validateCustomSectorLabel(ExecutionContextInterface $context): void
    {
        if ($this->sector?->isOther() && !$this->customSectorLabel) {
            $context->buildViolation('Merci de préciser votre secteur d\'activité.')
                ->atPath('customSectorLabel')
                ->addViolation();
        }
    }
}
