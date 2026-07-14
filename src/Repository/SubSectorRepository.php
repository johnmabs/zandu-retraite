<?php

namespace App\Repository;

use App\Entity\Sector;
use App\Entity\SubSector;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SubSector>
 */
class SubSectorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubSector::class);
    }

    public function findBySector(Sector $sector): array
    {
        return $this->createQueryBuilder('sub')
            ->andWhere('sub.sector = :sector')
            ->setParameter('sector', $sector)
            ->orderBy('sub.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
