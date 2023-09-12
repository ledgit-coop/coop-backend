<?php

namespace App\Helpers;

use App\Models\MemberAccount;

class AccountHelper {
    public static function generateAccount() {
        $member = MemberAccount::orderBy('account_number', 'desc')->first();

        if($member)
            return (int) $member->account_number + 1;

        return 1000001;
    }
}