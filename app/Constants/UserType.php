<?php

namespace App\Constants;

class UserType
{
    const REGULAR = 'regular';
    const ADMIN = 'admin';
    const SYSTEM = 'system';

    const LIST = [
        self::REGULAR,
        self::ADMIN,
        self::SYSTEM,
    ];
}