<?php

namespace App\Helpers;

use App\Models\Account;
use App\Models\Member;

class MemberHelper {
    public static function generateID() {
        $member = Member::orderBy('member_number', 'desc')->first();

        if($member)
            return (int) $member->member_number + 1;

        return 1000001;
    }

    public static function memberCreateDefaults(Member $member) {
        $share_capital = Account::where('key', 'share-capital')->firstOrFail();
        $savings = Account::where('key', 'regular-savings')->firstOrFail();

        $member->member_accounts()->createMany([
            ['account_id' => $share_capital->id, 'account_number' => bin2hex(random_bytes(rand(7,6)))],
            ['account_id' => $savings->id, 'account_number' => bin2hex(random_bytes(rand(7,6)))],
        ]);
    }
}