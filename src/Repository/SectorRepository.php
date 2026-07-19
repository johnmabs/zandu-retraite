<?php

namespace App\Repository;

use App\Entity\Sector;
use App\Enum\MemberStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UuidType;

/**
 * @extends ServiceEntityRepository<Sector>
 */
class SectorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sector::class);
    }

    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('s')
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByCode(string $code): ?Sector
    {
        return $this->findOneBy(['code' => $code]);
    }

    public function codeExists(string $code, ?Sector $excludeSector = null): bool
    {
        $qb = $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->andWhere('s.code = :code')
            ->setParameter('code', $code);

        if ($excludeSector) {
            $qb->andWhere('s.id != :excludeId')
                ->setParameter('excludeId', $excludeSector->getId(), UuidType::NAME);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function findAllWithSubSectors(): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.subSectors', 'sub')
            ->addSelect('sub')
            ->orderBy('s.name', 'ASC')
            ->addOrderBy('sub.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countActiveMembersBySector(): array
    {
        return $this->createQueryBuilder('s')
            ->select('s.id AS sectorId', 's.name AS sectorName', 'COUNT(m.id) AS total')
            ->leftJoin('App\Entity\Member', 'm', 'WITH', 'm.sector = s AND m.status = :active')
            ->setParameter('active', MemberStatus::Active->value)
            ->groupBy('s.id')
            ->orderBy('total', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }
}
