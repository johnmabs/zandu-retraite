<?php

namespace App\Tests\Functional\Payment;

use App\Entity\Payment;
use App\Enum\AdminRole;
use App\Enum\PaymentConfirmationMethod;
use App\Enum\PaymentMethod;
use App\Enum\PaymentSource;
use App\Enum\PaymentStatus;
use App\Factory\AdminUserFactory;
use App\Factory\MemberFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;

class PaymentApprovalTest extends WebTestCase
{
    use Factories;

    public function testAdminCanApproveAPendingBankTransfer(): void
    {
        $client = static::createClient();

        $admin = AdminUserFactory::createOne([
            'login' => 'admin.test.payment',
            'pin' => password_hash('1234', PASSWORD_BCRYPT),
            'role' => AdminRole::SuperAdmin,
        ]);

        $member = MemberFactory::createOne();
        $em = self::getContainer()->get('doctrine')->getManager();

        $payment = new Payment();
        $payment->setMember($member)
            ->setAmount('3000.00')
            ->setPaymentDate(new \DateTimeImmutable())
            ->setPaymentMethod(PaymentMethod::BankTransfer)
            ->setSource(PaymentSource::MemberDeclared)
            ->setConfirmationMethod(PaymentConfirmationMethod::ManualReview)
            ->setStatus(PaymentStatus::Pending);
        $em->persist($payment);
        $em->flush();

        $paymentId = $payment->getId();

        $client->loginUser($admin, 'admin_area');

        $client->request('GET', '/admin/payments/pending');
        self::assertResponseIsSuccessful();

        $client->submitForm('Valider');

        self::assertResponseRedirects('/admin/payments/pending');

        // Le kernel redémarre à chaque requête HTTP du client de test : on ne
        // peut pas fiabiliser $em/$payment capturés avant la requête (l'ancien
        // EntityManager est fermé). On récupère un EntityManager frais et on
        // re-fetch par id plutôt que d'appeler refresh() sur une référence
        // potentiellement obsolète.
        $freshEm = self::getContainer()->get('doctrine')->getManager();
        $refreshedPayment = $freshEm->getRepository(Payment::class)->find($paymentId);

        self::assertNotNull($refreshedPayment);
        self::assertSame(PaymentStatus::Confirmed, $refreshedPayment->getStatus());
    }
}
