<?php

namespace App\Constants;

class FinancialTypes {
    
    const REVENUES = 'revenues';
    const EXPENSES = 'expenses';
    const RECEIVABLES = 'receivables';
    const PAYABLES = 'payables';
    const LIABILITIES = 'liabilities';

    const LIST = [
        self::RECEIVABLES,
        self::EXPENSES,
        self::LIABILITIES,
        self::PAYABLES,
        self::REVENUES,
    ];
}