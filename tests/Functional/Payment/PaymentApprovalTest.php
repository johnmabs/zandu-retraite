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

/**
 * Couvre la régression #[IsCsrfTokenValid] (redirection silencieuse vers
 * le login au lieu d'un 403 sur token invalide — voir récapitulatif §9) :
 * en soumettant le vrai formulaire rendu (avec son vrai token généré),
 * ce test échoue si la vérification CSRF casse à nouveau silencieusement.
 */
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

        $client->loginUser($admin, 'admin_area');

        $client->request('GET', '/admin/payments/pending');
        self::assertResponseIsSuccessful();

        $client->submitForm('Valider');

        self::assertResponseRedirects('/admin/payments/pending');

        $em->refresh($payment);
        self::assertSame(PaymentStatus::Confirmed, $payment->getStatus());
    }
}
