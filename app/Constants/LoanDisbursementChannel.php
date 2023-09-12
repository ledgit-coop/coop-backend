<?php

namespace App\Constants;

class LoanDisbursementChannel
{
    const CASH = 'cash';
    const CHEQUE = 'cheque';
    const BANK_TRANSFER = 'bank-transfer';
    const E_WALLET_TRANSFER = 'e-wallet-transfer';

    const LIST = [
        self::CASH,
        self::CHEQUE,
        self::BANK_TRANSFER,
        self::E_WALLET_TRANSFER,
    ];
}