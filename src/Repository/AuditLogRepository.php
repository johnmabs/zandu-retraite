<?php

namespace App\Repository;

use App\Entity\AdminUser;
use App\Entity\AuditLog;
use App\Entity\Member;
use App\Enum\AdminRole;
use App\Enum\AuditEventType;
use App\Security\AuditVisibilityResolver;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AuditLog>
 */
class AuditLogRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly AuditVisibilityResolver $visibilityResolver,
    ) {
        parent::__construct($registry, AuditLog::class);
    }

    public function record(AuditLog $auditLog): void
    {
        $this->getEntityManager()->persist($auditLog);
        $this->getEntityManager()->flush();
    }

    public function findVisibleFor(AdminRole $role, int $page = 1, int $perPage = 50): Paginator
    {
        $visibleTypes = array_map(fn(AuditEventType $t) => $t->value, $this->visibilityResolver->visibleTypesFor($role));

        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.eventType IN (:types)')
            ->setParameter('types', $visibleTypes)
            ->orderBy('a.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        return new Paginator($qb->getQuery());
    }

    public function search(
        AdminRole $viewerRole,
        ?AuditEventType $eventType = null,
        ?\DateTimeImmutable $from = null,
        ?\DateTimeImmutable $to = null,
        int $page = 1,
        int $perPage = 50,
    ): Paginator {
        $visibleTypes = $this->visibilityResolver->visibleTypesFor($viewerRole);

        if ($eventType) {
            $visibleTypes = \in_array($eventType, $visibleTypes, true) ? [$eventType] : [];
        }

        $visibleTypeValues = array_map(fn(AuditEventType $t) => $t->value, $visibleTypes);

        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.eventType IN (:types)')
            ->setParameter('types', $visibleTypeValues)
            ->orderBy('a.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        if ($from) {
            $qb->andWhere('a.createdAt >= :from')->setParameter('from', $from);
        }

        if ($to) {
            $qb->andWhere('a.createdAt <= :to')->setParameter('to', $to);
        }

        return new Paginator($qb->getQuery());
    }

    public function findByActorAdmin(AdminUser $admin, int $limit = 100): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.actorAdmin = :admin')
            ->setParameter('admin', $admin)
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByActorMember(Member $member, int $limit = 100): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.actorMember = :member')
            ->setParameter('member', $member)
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countRecentFailedLoginsByIp(string $ipAddress, \DateTimeImmutable $since): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->andWhere('a.eventType IN (:types)')
            ->andWhere('a.ipAddress = :ip')
            ->andWhere('a.createdAt >= :since')
            ->setParameter('types', [AuditEventType::MemberLoginFailed->value, AuditEventType::AdminLoginFailed->value])
            ->setParameter('ip', $ipAddress)
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
