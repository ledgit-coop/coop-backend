<?php

namespace App\Constants;

class LoanRepaymentCycle {
    const DAILY = 'daily';
    const WEEKLY = 'weekly';
    const BIWEEKLY = 'biweekly';
    const MONTHLY = 'monthly';
    const BIMONTHLY = 'bimonthly';
    const QUARTERLY = 'quarterly';
    const YEARLY = 'yearly';

    const LIST = [
        self::DAILY,
        self::WEEKLY,
        self::BIWEEKLY,
        self::MONTHLY,
        self::BIMONTHLY,
        self::QUARTERLY,
        self::YEARLY,
    ];
}