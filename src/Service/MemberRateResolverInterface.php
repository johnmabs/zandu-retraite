<?php

namespace App\Service;

use App\Entity\Member;

interface MemberRateResolverInterface
{
    /** @return array{pension: string, management: string, cnss: string} */
    public function resolve(Member $member): array;
}
