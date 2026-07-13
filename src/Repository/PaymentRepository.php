<?php

namespace App\Repository;

use App\Entity\Member;
use App\Entity\Payment;
use App\Enum\PaymentMethod;
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

    // Historique complet d'un membre, plus récent en premier (écran "Historique" client)
    public function findByMember(Member $member): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.member = :member')
            ->setParameter('member', $member)
            ->orderBy('p.paymentDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // Versements confirmés d'un membre sur une période donnée — utilisé pour générer un
    // bulletin de paie (fenêtre glissante) sans dépendre d'un recalcul depuis tout l'historique
    public function findConfirmedByMemberAndPeriod(
        Member $member,
        \DateTimeImmutable $start,
        \DateTimeImmutable $end,
    ): array {
        return $this->createQueryBuilder('p')
            ->andWhere('p.member = :member')
            ->andWhere('p.status = :status')
            ->andWhere('p.paymentDate BETWEEN :start AND :end')
            ->setParameter('member', $member)
            ->setParameter('status', PaymentStatus::Confirmed)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('p.paymentDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // Somme totale confirmée pour un membre — utilisé pour la projection de capital
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

    // File d'attente des versements à valider manuellement (ex: virements bancaires)
    public function findPendingManualReview(int $limit = 50): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->andWhere('p.confirmationMethod = :method')
            ->setParameter('status', PaymentStatus::Pending)
            ->setParameter('method', \App\Enum\PaymentConfirmationMethod::ManualReview)
            ->orderBy('p.createdAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche paginée pour l'écran admin "Gestion des versements".
     *
     * @return Paginator<Payment>
     */
    public function search(
        ?PaymentStatus $status = null,
        ?PaymentMethod $method = null,
        ?Member $member = null,
        ?\DateTimeImmutable $from = null,
        ?\DateTimeImmutable $to = null,
        int $page = 1,
        int $perPage = 25,
    ): Paginator {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.member', 'm')
            ->addSelect('m')
            ->orderBy('p.paymentDate', 'DESC');

        if ($status) {
            $qb->andWhere('p.status = :status')->setParameter('status', $status);
        }

        if ($method) {
            $qb->andWhere('p.paymentMethod = :method')->setParameter('method', $method);
        }

        if ($member) {
            $qb->andWhere('p.member = :member')->setParameter('member', $member);
        }

        if ($from) {
            $qb->andWhere('p.paymentDate >= :from')->setParameter('from', $from);
        }

        if ($to) {
            $qb->andWhere('p.paymentDate <= :to')->setParameter('to', $to);
        }

        $qb->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        return new Paginator($qb->getQuery());
    }

    // Total collecté sur une période, tous membres confondus — carte stat du dashboard admin
    public function totalConfirmedBetween(\DateTimeImmutable $from, \DateTimeImmutable $to): string
    {
        $result = $this->createQueryBuilder('p')
            ->select('COALESCE(SUM(p.amount), 0) AS total')
            ->andWhere('p.status = :status')
            ->andWhere('p.paymentDate BETWEEN :from AND :to')
            ->setParameter('status', PaymentStatus::Confirmed)
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->getQuery()
            ->getSingleScalarResult();

        return (string) $result;
    }

    // Répartition par moyen de paiement, pour un graphique du dashboard admin
    public function countByMethod(): array
    {
        $rows = $this->createQueryBuilder('p')
            ->select('p.paymentMethod AS method, COUNT(p.id) AS total')
            ->andWhere('p.status = :status')
            ->setParameter('status', PaymentStatus::Confirmed)
            ->groupBy('p.paymentMethod')
            ->getQuery()
            ->getArrayResult();

        $counts = array_fill_keys(array_map(fn(PaymentMethod $m) => $m->value, PaymentMethod::cases()), 0);
        foreach ($rows as $row) {
            $counts[$row['method']->value] = (int) $row['total'];
        }

        return $counts;
    }
}
