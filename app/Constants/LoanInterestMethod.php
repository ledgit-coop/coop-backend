<?php

namespace App\Constants;

class LoanInterestMethod
{
    const FLAT_RATE = 'flat-rate';
    const REDUCING_BALANCE_EQUAL_INSTALLMENTS = 'reducing-bal-eq-installments';
    const REDUCING_BALANCE_EQUAL_PRINCIPAL = 'reducing-bal-eq-principal';

    const LIST = [
        self::FLAT_RATE,
        self::REDUCING_BALANCE_EQUAL_INSTALLMENTS,
        self::REDUCING_BALANCE_EQUAL_PRINCIPAL,
    ];
}