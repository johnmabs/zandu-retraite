<?php

namespace App\Repository;

use App\Entity\Member;
use App\Entity\Payment;
use App\Enum\PaymentConfirmationMethod;
use App\Enum\PaymentStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Payment>
 */
class PaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payment::class);
    }

    public function findByMember(Member $member, int $page = 1, int $perPage = 20): Paginator
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.member = :member')
            ->setParameter('member', $member)
            ->orderBy('p.paymentDate', 'DESC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        return new Paginator($qb->getQuery());
    }

    public function findConfirmedByMemberAndPeriod(
        Member $member,
        \DateTimeImmutable $periodStart,
        \DateTimeImmutable $periodEnd,
    ): array {
        return $this->createQueryBuilder('p')
            ->andWhere('p.member = :member')
            ->andWhere('p.status = :status')
            ->andWhere('p.paymentDate BETWEEN :start AND :end')
            ->setParameter('member', $member)
            ->setParameter('status', PaymentStatus::Confirmed->value)
            ->setParameter('start', $periodStart)
            ->setParameter('end', $periodEnd)
            ->orderBy('p.paymentDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function sumConfirmedAmountByMember(Member $member): string
    {
        $result = $this->createQueryBuilder('p')
            ->select('COALESCE(SUM(p.amount), 0) AS total')
            ->andWhere('p.member = :member')
            ->andWhere('p.status = :status')
            ->setParameter('member', $member)
            ->setParameter('status', PaymentStatus::Confirmed->value)
            ->getQuery()
            ->getSingleScalarResult();

        return (string) $result;
    }

    public function findAwaitingManualReview(int $limit = 50): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.member', 'm')
            ->addSelect('m')
            ->andWhere('p.status = :status')
            ->andWhere('p.confirmationMethod = :method')
            ->setParameter('status', PaymentStatus::Pending->value)
            ->setParameter('method', PaymentConfirmationMethod::ManualReview->value)
            ->orderBy('p.createdAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function sumConfirmedAmountByMethod(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.paymentMethod AS method', 'COALESCE(SUM(p.amount), 0) AS total', 'COUNT(p.id) AS count')
            ->andWhere('p.status = :status')
            ->andWhere('p.paymentDate BETWEEN :from AND :to')
            ->setParameter('status', PaymentStatus::Confirmed->value)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->groupBy('p.paymentMethod')
            ->getQuery()
            ->getArrayResult();
    }

    public function findLastConfirmedForMember(Member $member): ?Payment
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.member = :member')
            ->andWhere('p.status = :status')
            ->setParameter('member', $member)
            ->setParameter('status', PaymentStatus::Confirmed->value)
            ->orderBy('p.paymentDate', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
