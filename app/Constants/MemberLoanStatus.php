<?php

namespace App\Constants;

class MemberLoanStatus
{
    const EVALUATION = 'evaluation';
    const PRE_APPROVED = 'pre-approved';
    const PENDING = 'pending';
    const APPROVED = 'approved';
    const REJECTED = 'rejected';
    const CLOSED = 'closed';
    const OVERDUE = 'overdue';
    const RELEASED = 'released';
    const REQUEST_PRE_TERMINATION = 'request-pre-termination';
    const PRE_TERMINATED = 'pre-terminated';

    const LIST = [
        self::EVALUATION,
        self::APPROVED,
        self::PRE_APPROVED,
        self::PENDING,
        self::REJECTED,
        self::CLOSED,
        self::OVERDUE,
        self::RELEASED,
        self::REQUEST_PRE_TERMINATION,
        self::PRE_TERMINATED,
    ];
}