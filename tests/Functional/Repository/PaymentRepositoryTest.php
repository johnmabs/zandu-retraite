<?php

namespace App\Tests\Functional\Repository;

use App\Entity\Payment;
use App\Enum\PaymentConfirmationMethod;
use App\Enum\PaymentMethod;
use App\Enum\PaymentSource;
use App\Enum\PaymentStatus;
use App\Factory\MemberFactory;
use App\Repository\PaymentRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;

/**
 * Verrouille deux bugs Doctrine découverts en production de ce projet :
 * (1) un enum brut passé à setParameter() ne se convertit pas fiablement
 * en sa valeur scalaire, (2) une entité brute passée à setParameter() pour
 * une colonne de type 'uuid' custom n'est pas correctement liée en binaire.
 * Les deux causaient un retour vide/zéro silencieux, SANS exception — donc
 * seul un test qui vérifie la vraie valeur retournée (pas juste l'absence
 * d'erreur) peut les détecter. Voir le récapitulatif du projet, §9.
 */
class PaymentRepositoryTest extends KernelTestCase
{
    use Factories;

    public function testSumConfirmedAmountByMemberReturnsCorrectTotal(): void
    {
        $member = MemberFactory::createOne();
        $em = self::getContainer()->get('doctrine')->getManager();

        $this->persistPayment($em, $member, '1000.00', PaymentStatus::Confirmed);
        $this->persistPayment($em, $member, '500.00', PaymentStatus::Confirmed);
        $this->persistPayment($em, $member, '999.00', PaymentStatus::Pending);
        $em->flush();

        /** @var PaymentRepository $repository */
        $repository = self::getContainer()->get(PaymentRepository::class);

        self::assertSame(
            '1500.00',
            $repository->sumConfirmedAmountByMember($member),
            'Seuls les versements confirmés doivent être sommés, avec le bon lien vers le membre.',
        );
    }

    public function testFindByMemberOnlyReturnsThatMembersPayments(): void
    {
        $memberA = MemberFactory::createOne();
        $memberB = MemberFactory::createOne();
        $em = self::getContainer()->get('doctrine')->getManager();

        $this->persistPayment($em, $memberA, '1000.00', PaymentStatus::Confirmed);
        $this->persistPayment($em, $memberB, '2000.00', PaymentStatus::Confirmed);
        $em->flush();

        /** @var PaymentRepository $repository */
        $repository = self::getContainer()->get(PaymentRepository::class);

        $results = iterator_to_array($repository->findByMember($memberA)->getIterator());

        self::assertCount(1, $results);
        self::assertSame('1000.00', $results[0]->getAmount());
    }

    private function persistPayment(
        \Doctrine\ORM\EntityManagerInterface $em,
        \App\Entity\Member $member,
        string $amount,
        PaymentStatus $status,
    ): void {
        $payment = new Payment();
        $payment->setMember($member)
            ->setAmount($amount)
            ->setPaymentDate(new \DateTimeImmutable())
            ->setPaymentMethod(PaymentMethod::Cash)
            ->setSource(PaymentSource::AdminRecorded)
            ->setConfirmationMethod(PaymentConfirmationMethod::ManualReview)
            ->setStatus($status);

        $em->persist($payment);
    }
}
