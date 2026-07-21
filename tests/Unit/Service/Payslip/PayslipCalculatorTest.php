<?php

namespace App\Tests\Unit\Service\Payslip;

use App\Entity\Member;
use App\Entity\Payment;
use App\Service\MemberRateResolverInterface;
use App\Service\Payslip\PayslipCalculator;
use PHPUnit\Framework\TestCase;

class PayslipCalculatorTest extends TestCase
{
    public function testCalculateSplitsGrossAmountAccordingToRates(): void
    {
        $rateResolver = $this->createStub(MemberRateResolverInterface::class);
        $rateResolver->method('resolve')->willReturn([
            'pension' => '70.00',
            'management' => '15.00',
            'cnss' => '10.00',
        ]);

        $calculator = new PayslipCalculator($rateResolver);

        $payment1 = new Payment();
        $payment1->setAmount('1000.00');
        $payment2 = new Payment();
        $payment2->setAmount('2000.00');

        $result = $calculator->calculate(new Member(), [$payment1, $payment2]);

        self::assertSame('3000.00', $result->grossAmount);
        self::assertSame('2100.00', $result->pensionShareAmount);
        self::assertSame('450.00', $result->managementFeeAmount);
        self::assertSame('300.00', $result->cnssContributionAmount);
        self::assertSame('2100.00', $result->netAmount);
        self::assertSame(2, $result->paymentsCount);
    }

    public function testCalculateWithNoPaymentsReturnsZero(): void
    {
        $rateResolver = $this->createStub(MemberRateResolverInterface::class);
        $rateResolver->method('resolve')->willReturn(['pension' => '70.00', 'management' => '15.00', 'cnss' => '10.00']);

        $result = (new PayslipCalculator($rateResolver))->calculate(new Member(), []);

        self::assertSame('0.00', $result->grossAmount);
        self::assertSame(0, $result->paymentsCount);
    }
}
