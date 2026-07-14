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

    // Liste complète triée, pour les <select> de formulaire (inscription, filtres admin)
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

    // Charge secteurs + sous-secteurs en une seule requête (évite le N+1 du formulaire
    // d'inscription en cascade : select secteur -> select sous-secteurs dépendants)
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

    // Nombre de membres actifs par secteur, pour le dashboard admin / répartition
    public function countActiveMembersBySector(): array
    {
        return $this->createQueryBuilder('s')
            ->select('s.id AS sectorId', 's.name AS sectorName', 'COUNT(m.id) AS total')
            ->leftJoin('App\Entity\Member', 'm', 'WITH', 'm.sector = s AND m.status = :active')
            ->setParameter('active', \App\Enum\MemberStatus::Active)
            ->groupBy('s.id')
            ->orderBy('total', 'DESC')
            ->getQuery()
            ->getArrayResult();
    }
}
