<?php

namespace App\Repository;

use App\Entity\IssuedContract;
use App\Entity\Member;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class IssuedContractRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IssuedContract::class);
    }

    public function findByMember(Member $member): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.member = :member')
            ->setParameter('member', $member)
            ->orderBy('c.issuedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
