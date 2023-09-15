<?php

namespace App\Constants;

class TransactionType
{
    const REVENUE = 'revenue';
    const EXPENSE = 'expense';
    const PAYMENT = 'payment';

    const LIST = [
        self::REVENUE,
        self::EXPENSE,
        self::PAYMENT,
    ];
}