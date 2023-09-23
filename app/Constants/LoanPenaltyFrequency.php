<?php

namespace App\Constants;

class LoanPenaltyFrequency
{
    const EVERY_DAY = 'every-day';
    const EVERY_WEEK = 'every-week';
    const EVERY_MONTH = 'every-month';
    const EVERY_YEAR = 'every-year';
    const EVERY_AMORTIZATION = 'every-amortization';

    const LIST = [
        self::EVERY_DAY,
        self::EVERY_WEEK,
        self::EVERY_MONTH,
        self::EVERY_YEAR,
        self::EVERY_AMORTIZATION,
    ];
}
