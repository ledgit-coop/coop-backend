<?php

namespace App\Constants;

class MemberLoanStatus
{
    const EVALUATION = 'evaluation';
    const PRE_APPROVED = 'pre-approved';
    const PENDING = 'pending';
    const APPROVED = 'approved';
    const REJECTED = 'rejected';
    const PAID = 'paid';
    const OVERDUE = 'overdue';

    const LIST = [
        self::APPROVED,
        self::PRE_APPROVED,
        self::PENDING,
        self::REJECTED,
        self::PAID,
        self::OVERDUE,
    ];
}