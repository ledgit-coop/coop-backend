<?php

namespace App\Helpers;

use App\Models\Account;
use App\Models\Member;
use App\Models\MemberAccount;
use Illuminate\Support\Carbon;

class MemberHelper {

    public static function generateID() {

        $currentYear = Carbon::now()->format('y');

        $member = Member::orderBy('member_number', 'desc')->first();

        if($member) {
            $numbers = explode("-",$member->member_number);
            $count = abs($numbers[count($numbers) - 1]);
        } else {
            $count = 0;
        }

        $sequence = sprintf('%08d', $count + 1);

        return  $currentYear . '-' . $sequence;;
    }

    public static function memberCreateDefaults(Member $member) {
        $share_capital = Account::where('key', 'share-capital')->firstOrFail();
        
        self::makeAccount($member, $share_capital, $member->full_name);
    }

    public static function makeAccount(Member $member, Account $account, string $holder) {
        return MemberAccount::create([
            'account_holder' => $holder,
            'account_number' => AccountHelper::generateAccount($member),
            'passbook_count' => 1,
            'member_id' => $member->id,
            'account_id' => $account->id,
            'balance' => 0,
            'earn_interest_per_anum' => $account->earn_interest_per_anum,
            'maintaining_balance' => $account->maintaining_balance,
            'penalty_below_maintaining_method' => $account->penalty_below_maintaining_method,
            'penalty_below_maintaining' => $account->penalty_below_maintaining,
            'penalty_below_maintaining_cycle' => $account->penalty_below_maintaining_cycle,
            'penalty_below_maintaining_duration' => $account->penalty_below_maintaining_duration,
        ]);
    }
}