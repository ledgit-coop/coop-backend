<?php

namespace App\Constants;

class LoanPenaltyMethod
{
    const PERCENTAGE = 'percentage';
    const FIX_AMOUNT = 'fix-amount';

    const LIST = [
        self::PERCENTAGE,
        self::FIX_AMOUNT,
    ];
}
