<?php

namespace App\Repository;

use App\Entity\Member;
use App\Entity\Payslip;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Payslip>
 */
class PayslipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payslip::class);
    }

    // Détermine le point de départ de la prochaine fenêtre de 30 jours
    public function findLastForMember(Member $member): ?Payslip
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.member = :member')
            ->setParameter('member', $member)
            ->orderBy('p.periodEnd', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByMember(Member $member, int $page = 1, int $perPage = 20): Paginator
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.member = :member')
            ->setParameter('member', $member)
            ->orderBy('p.periodEnd', 'DESC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        return new Paginator($qb->getQuery());
    }
}
