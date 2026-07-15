<?php

namespace App\Command;

use App\Entity\AdminUser;
use App\Entity\Member;
use App\Entity\Sector;
use App\Enum\AdminPermission;
use App\Enum\AdminRole;
use App\Enum\MemberStatus;
use App\Repository\AdminUserRepository;
use App\Repository\MemberRepository;
use App\Repository\SectorRepository;
use App\Service\MemberNumberGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:create-test-users', description: 'Crée un admin et un membre de test (dev uniquement, idempotent)')]
class CreateTestUsersCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly SectorRepository $sectorRepository,
        private readonly AdminUserRepository $adminUserRepository,
        private readonly MemberRepository $memberRepository,
        private readonly MemberNumberGenerator $memberNumberGenerator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $sector = $this->sectorRepository->findOneBy(['code' => 'COMMERCE']);
        if (!$sector) {
            $sector = new Sector();
            $sector->setName('Commerce général')->setCode('COMMERCE');
            $this->em->persist($sector);
            $io->comment('Secteur créé.');
        } else {
            $io->comment('Secteur déjà existant, réutilisé.');
        }

        $admin = $this->adminUserRepository->findOneBy(['login' => 'admin.test']);
        if (!$admin) {
            $admin = new AdminUser();
            $admin->setFullName('Admin Test')
                ->setLogin('admin.test')
                ->setRole(AdminRole::SuperAdmin)
                ->setPermissions(AdminPermission::cases());
            $admin->setPin($this->passwordHasher->hashPassword($admin, '1234'));
            $this->em->persist($admin);
            $io->comment('Admin créé.');
        } else {
            $io->comment('Admin déjà existant, réutilisé.');
        }

        $member = $this->memberRepository->findOneBy(['phone' => '+242060000000']);
        if (!$member) {
            $member = new Member();
            $member->setMemberNumber($this->memberNumberGenerator->generate())
                ->setFirstName('Jean')
                ->setLastName('Test')
                ->setPhone('+242060000000')
                ->setSector($sector)
                ->setDailyPaymentAmount('1000.00')
                ->setStatus(MemberStatus::Active)
                ->setRegisteredAt(new \DateTimeImmutable());
            $member->setPin($this->passwordHasher->hashPassword($member, '5678'));
            $this->em->persist($member);
            $io->comment('Membre créé.');
        } else {
            $io->comment('Membre déjà existant, réutilisé.');
        }

        $this->em->flush();

        $io->success('OK — admin login=admin.test / PIN=1234 — membre phone=+242060000000 / PIN=5678');

        return Command::SUCCESS;
    }
}
