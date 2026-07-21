<?php

namespace App\Tests\Functional\Security;

use App\Enum\MemberStatus;
use App\Factory\MemberFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;

class MemberLoginTest extends WebTestCase
{
    use Factories;

    public function testActiveMemberCanLogInWithCorrectPin(): void
    {
        $client = static::createClient();

        MemberFactory::createOne([
            'phone' => '+242061112222',
            'pin' => password_hash('4242', PASSWORD_BCRYPT),
            'status' => MemberStatus::Active,
        ]);

        $client->request('GET', '/member-area/login');
        $client->submitForm('Se connecter', ['_phone' => '+242061112222', '_pin' => '4242']);

        self::assertResponseRedirects('/member-area');
    }

    public function testPendingMemberIsBlockedWithClearMessage(): void
    {
        $client = static::createClient();

        MemberFactory::createOne([
            'phone' => '+242063334444',
            'pin' => password_hash('4242', PASSWORD_BCRYPT),
            'status' => MemberStatus::Pending,
        ]);

        $client->request('GET', '/member-area/login');
        $client->submitForm('Se connecter', ['_phone' => '+242063334444', '_pin' => '4242']);
        $client->followRedirect();

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.frais-box', 'attente de validation');
    }

    public function testWrongPinDoesNotAuthenticate(): void
    {
        $client = static::createClient();

        MemberFactory::createOne([
            'phone' => '+242065556666',
            'pin' => password_hash('4242', PASSWORD_BCRYPT),
            'status' => MemberStatus::Active,
        ]);

        $client->request('GET', '/member-area/login');
        $client->submitForm('Se connecter', ['_phone' => '+242065556666', '_pin' => '0000']);
        $client->followRedirect();

        self::assertSelectorExists('.frais-box');
    }
}
