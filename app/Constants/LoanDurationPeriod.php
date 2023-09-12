<?php

namespace App\Constants;

class LoanDurationPeriod
{
    const DAYS = 'days';
    const WEEKS = 'weeks';
    const MONTHS = 'months';
    const YEARS = 'years';

    const LIST = [
        self::DAYS,
        self::WEEKS,
        self::MONTHS,
        self::YEARS,
    ];
}