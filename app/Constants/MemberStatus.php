<?php

namespace App\Constants;

class MemberStatus
{
    const TERMINATED = 'terminated';
    const ACTIVE = 'active';
     

    const LIST = [
        self::TERMINATED,
        self::ACTIVE,
    ];
}