<?php

namespace App\Constants;

class LoanInterestPeriod
{
    const PER_DAY = 'per-day';
    const PER_WEEK = 'per-week';
    const PER_MONTH = 'per-month';
    const PER_YEAR = 'per-year';
    const PER_LOAN = 'per-loan';
 
    const LIST = [
        self::PER_DAY,
        self::PER_WEEK,
        self::PER_MONTH,
        self::PER_YEAR,
        self::PER_LOAN,
    ];
}