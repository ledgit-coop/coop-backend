<?php

namespace App\Constants;

class AddressResidencyStatus
{
    const PRESENT = 'present';
    const PERMANENT = 'permanent';
    
    const LIST = [
        self::PRESENT,
        self::PERMANENT,
    ];
}