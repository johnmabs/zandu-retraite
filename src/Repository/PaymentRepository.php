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

    // Historique paginé d'un membre, le plus récent d'abord (écran "Historique" côté client)
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

    // Versements confirmés d'un membre sur une période, base du calcul de bulletin de paie
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
            ->setParameter('status', PaymentStatus::Confirmed)
            ->setParameter('start', $periodStart)
            ->setParameter('end', $periodEnd)
            ->orderBy('p.paymentDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // Total confirmé cumulé d'un membre depuis son inscription, pour la projection de capital
    public function sumConfirmedAmountByMember(Member $member): string
    {
        $result = $this->createQueryBuilder('p')
            ->select('COALESCE(SUM(p.amount), 0) AS total')
            ->andWhere('p.member = :member')
            ->andWhere('p.status = :status')
            ->setParameter('member', $member)
            ->setParameter('status', PaymentStatus::Confirmed)
            ->getQuery()
            ->getSingleScalarResult();

        return (string) $result;
    }

    // File d'attente de validation manuelle pour les admins (ex: virements à vérifier)
    public function findAwaitingManualReview(int $limit = 50): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.member', 'm')
            ->addSelect('m')
            ->andWhere('p.status = :status')
            ->andWhere('p.confirmationMethod = :method')
            ->setParameter('status', PaymentStatus::Pending)
            ->setParameter('method', PaymentConfirmationMethod::ManualReview)
            ->orderBy('p.createdAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    // Répartition des versements confirmés par moyen de paiement sur une période, pour le dashboard admin
    public function sumConfirmedAmountByMethod(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.paymentMethod AS method', 'COALESCE(SUM(p.amount), 0) AS total', 'COUNT(p.id) AS count')
            ->andWhere('p.status = :status')
            ->andWhere('p.paymentDate BETWEEN :from AND :to')
            ->setParameter('status', PaymentStatus::Confirmed)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->groupBy('p.paymentMethod')
            ->getQuery()
            ->getArrayResult();
    }

    // Dernier versement confirmé d'un membre, utile pour détecter les impayés prolongés
    public function findLastConfirmedForMember(Member $member): ?Payment
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.member = :member')
            ->andWhere('p.status = :status')
            ->setParameter('member', $member)
            ->setParameter('status', PaymentStatus::Confirmed)
            ->orderBy('p.paymentDate', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
