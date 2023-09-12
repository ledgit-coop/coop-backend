<?php

namespace App\Constants;

class AccountType
{
    const REGULAR = 'regular';
    const SAVINGS = 'savings';
    const SHARE_CAPITAL = 'share-capita';

    const LIST = [
        self::REGULAR,
        self::SAVINGS,
        self::SHARE_CAPITAL,
    ];
}