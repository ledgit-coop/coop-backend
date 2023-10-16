<?php

namespace App\Constants;

class TransactionSubTypes
{
    const MEMBERSHIP_FEE = 'membership-fee';
    const ORIENTATION_FEE = 'orientation-fee';


    const LIST = [
        self::MEMBERSHIP_FEE,
        self::ORIENTATION_FEE
    ];
}