<?php

namespace App\Constants;

use App\Models\Loan;
use App\Models\Member;

class LogModules {
    
    const MODULE_MEMBER = Member::class;
    const LOAN = Loan::class;
  
    const MODULES = [
        self::MODULE_MEMBER,
        self::LOAN
    ];
}