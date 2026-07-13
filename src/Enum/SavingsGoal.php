<?php

namespace App\Enum;

enum SavingsGoal: string
{
    case ComfortableRetirement = 'comfortable_retirement';
    case ChildrenEducation = 'children_education';
    case RealEstatePurchase = 'real_estate_purchase';
    case StartingCapital = 'starting_capital';
    case Other = 'other'; // détail libre dans Member::goalDetails
}
