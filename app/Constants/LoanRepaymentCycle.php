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
    const LUMP_SUM = 'lump-sum';

    const LIST = [
        self::DAILY,
        self::WEEKLY,
        self::BIWEEKLY,
        self::MONTHLY,
        self::BIMONTHLY,
        self::QUARTERLY,
        self::YEARLY,
        self::LUMP_SUM,
    ];

    const CARBON = [
        self::DAILY => ['day', 1],
        self::WEEKLY => ['week', 1],
        self::BIWEEKLY => ['week', 2],
        self::MONTHLY => ['month', 1],
        self::BIMONTHLY => ['month', 2],
        self::QUARTERLY => ['quarter', 1],
        self::YEARLY => ['year', 1],
        self::LUMP_SUM => ['day', 0],
    ];
}