<?php

namespace App\Constants;

class TransactionSubTypes
{
    const MEMBERSHIP_FEE = 'membership-fee';
    const ORIENTATION_FEE = 'orientation-fee';
    const INTEREST_EARNED = 'interest-earned';
    const LOAN_PAYMENT = 'loan-payment';
    const LOAN_FEES = 'loan-fees';
    const LOAN_RELEASED = 'loan-released';
    const LOAN_CREDIT = 'loan-credit';
    const SHARE_CAPITAL = 'share-capital';
    const LOAN_DISBURSEMENT = 'loan-disbursement';

    const LOAN_PRINCIPAL_PAYMENT = 'loan-principal-payment';
    const LOAN_INTEREST_PAYMENT = 'loan-interest-payment';
    const LOAN_PENALTIES_PAYMENT = 'loan-penalties-payment';

    const LIST = [
        self::MEMBERSHIP_FEE,
        self::ORIENTATION_FEE,
        self::INTEREST_EARNED,
        self::LOAN_PAYMENT,
        self::LOAN_FEES,
        self::LOAN_RELEASED,
        self::LOAN_CREDIT,
        self::SHARE_CAPITAL,
    ];
}