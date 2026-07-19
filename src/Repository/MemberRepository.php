<?php

namespace App\Repository;

use App\Entity\Member;
use App\Entity\Sector;
use App\Enum\MemberStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Types\UuidType;

/**
 * @extends ServiceEntityRepository<Member>
 */
class MemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Member::class);
    }

    public function findOneByPhone(string $phone): ?Member
    {
        return $this->findOneBy(['phone' => $phone]);
    }


    public function findPendingRegistrations(int $limit = 50): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.status = :status')
            ->setParameter('status', MemberStatus::Pending->value)
            ->orderBy('m.createdAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function phoneExists(string $phone, ?Member $excludeMember = null): bool
    {
        $qb = $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->andWhere('m.phone = :phone')
            ->setParameter('phone', $phone);

        if ($excludeMember) {
            $qb->andWhere('m.id != :excludeId')
                ->setParameter('excludeId', $excludeMember->getId(), UuidType::NAME);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function findActiveBySector(Sector $sector): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.sector = :sector')
            ->andWhere('m.status = :status')
            ->setParameter('sector', $sector->getId(), UuidType::NAME)
            ->setParameter('status', MemberStatus::Active->value)
            ->orderBy('m.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Paginator<Member>
     */
    public function search(
        ?MemberStatus $status = null,
        ?Sector $sector = null,
        ?string $searchTerm = null,
        int $page = 1,
        int $perPage = 25,
    ): Paginator {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.sector', 's', Join::WITH)
            ->addSelect('s')
            ->orderBy('m.createdAt', 'DESC');

        if ($status) {
            $qb->andWhere('m.status = :status')->setParameter('status', $status->value);
        }

        if ($sector) {
    $qb->andWhere('m.sector = :sector')->setParameter('sector', $sector->getId(), UuidType::NAME);
}

        if ($searchTerm) {
            $qb->andWhere('m.firstName LIKE :term OR m.lastName LIKE :term OR m.memberNumber LIKE :term OR m.phone LIKE :term')
                ->setParameter('term', '%' . $searchTerm . '%');
        }

        $qb->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        return new Paginator($qb->getQuery());
    }

    public function countByStatus(): array
    {
        $rows = $this->createQueryBuilder('m')
            ->select('m.status AS status, COUNT(m.id) AS total')
            ->groupBy('m.status')
            ->getQuery()
            ->getArrayResult();

        $counts = array_fill_keys(array_map(fn(MemberStatus $s) => $s->value, MemberStatus::cases()), 0);
        foreach ($rows as $row) {
            // m.status est hydraté en enumType: retourne déjà un MemberStatus ici (SELECT, pas WHERE)
            $counts[$row['status']->value] = (int) $row['total'];
        }

        return $counts;
    }

    public function findLastMemberNumber(): ?string
    {
        $result = $this->createQueryBuilder('m')
            ->select('m.memberNumber')
            ->orderBy('m.memberNumber', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $result['memberNumber'] ?? null;
    }

    public function findAllActive(): iterable
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.status = :status')
            ->setParameter('status', MemberStatus::Active->value)
            ->getQuery()
            ->toIterable();
    }
}
