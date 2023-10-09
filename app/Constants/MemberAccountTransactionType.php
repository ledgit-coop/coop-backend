<?php

namespace App\Constants;

class MemberAccountTransactionType {
    const INTEREST_EARNED = 'interest-earned';
    const LOAN_PAYMENT = 'loan-payment';
    const LOAN_FEES = 'loan-fees';
    const LOAN_RELEASED = 'loan-released';
    const LOAN_CREDIT = 'loan-credit';

    const LIST = [
        self::INTEREST_EARNED,
        self::LOAN_PAYMENT,
        self::LOAN_FEES,
        self::LOAN_RELEASED,
        self::LOAN_CREDIT,
    ];
}