<?php

namespace App\Constants;

class LoanInterestType {

    const PERCENTAGE_BASE = 'percentage-base';
    const FIX_AMOUNT_CYCLE = 'fix-amount-per-cycle';

    const LIST = [
        self::PERCENTAGE_BASE,
        self::FIX_AMOUNT_CYCLE,
    ];

}