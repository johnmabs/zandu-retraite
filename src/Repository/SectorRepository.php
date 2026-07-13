<?php

namespace App\Repository;

use App\Entity\Sector;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sector>
 */
class SectorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sector::class);
    }

    // Liste triée pour les <select> des formulaires d'inscription/filtres
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
                ->setParameter('excludeId', $excludeSector->getId());
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    // Charge les secteurs avec leurs sous-secteurs en une seule requête (évite le N+1
    // classique quand on affiche la liste complète secteur > sous-secteurs en admin)
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

    // Répartition du nombre de membres par secteur, pour les stats admin
    public function countMembersBySector(): array
    {
        return $this->createQueryBuilder('s')
            ->select('s.id AS sectorId, s.name AS sectorName, COUNT(m.id) AS total')
            ->leftJoin('App\Entity\Member', 'm', 'WITH', 'm.sector = s')
            ->groupBy('s.id')
            ->orderBy('total', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }
}
