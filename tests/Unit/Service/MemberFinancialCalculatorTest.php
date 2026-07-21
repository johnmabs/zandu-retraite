<?php

namespace App\Tests\Unit\Service;

use App\Entity\Member;
use App\Entity\Setting;
use App\Enum\EngagementDuration;
use App\Enum\SalaryCategory;
use App\Repository\SettingRepository;
use App\Service\MemberFinancialCalculator;
use App\Service\MemberRateResolverInterface;
use PHPUnit\Framework\TestCase;

class MemberFinancialCalculatorTest extends TestCase
{
    public function testEstimateCapitalUsesSimpleProjectionFormula(): void
    {
        $rateResolver = $this->createMock(MemberRateResolverInterface::class);
        $rateResolver->method('resolve')->willReturn(['pension' => '70.00', 'management' => '15.00', 'cnss' => '10.00']);

        $settingRepository = $this->createMock(SettingRepository::class);

        $calculator = new MemberFinancialCalculator($settingRepository, $rateResolver);

        $member = new Member();
        $member->setDailyPaymentAmount('1000.00');
        $member->setEngagementDuration(EngagementDuration::TenYears);

        // 1000 x 365 x 10 x 70% = 2 555 000.00
        self::assertSame('2555000.00', $calculator->estimateCapital($member));
    }

    public function testResolveSalaryCategoryUsesConfiguredThresholds(): void
    {
        $setting = new Setting();
        $setting->setSalaryCategoryThresholds([
            'bronze' => ['min' => 0, 'max' => 1999],
            'silver' => ['min' => 2000, 'max' => 4999],
            'gold' => ['min' => 5000, 'max' => 9999],
            'platinum' => ['min' => 10000, 'max' => null],
        ]);

        $settingRepository = $this->createMock(SettingRepository::class);
        $settingRepository->method('getOrCreate')->willReturn($setting);

        $calculator = new MemberFinancialCalculator($settingRepository, $this->createMock(MemberRateResolverInterface::class));

        self::assertSame(SalaryCategory::Bronze, $calculator->resolveSalaryCategory('1500.00'));
        self::assertSame(SalaryCategory::Silver, $calculator->resolveSalaryCategory('3000.00'));
        self::assertSame(SalaryCategory::Gold, $calculator->resolveSalaryCategory('7000.00'));
        self::assertSame(SalaryCategory::Platinum, $calculator->resolveSalaryCategory('15000.00'));
    }
}
