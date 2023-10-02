<?php

namespace App\Helpers;

use App\Models\AccountTransaction;
use App\Models\Member;
use App\Models\MemberAccount;
use Illuminate\Support\Carbon;

class AccountHelper {

    public static function generateAccount(Member $member) {

        $account_count = $member->member_accounts->count();
        
        $numbers = explode("-",$member->member_number);
        $count = abs($numbers[count($numbers) - 1]);

        $currentYear = Carbon::now()->format('ym');
       
        return  $member->member_number .'-'. $currentYear . $count + ($account_count + 1);
    }

    public static function generateTransactionNumber() {

        $currentYear = Carbon::now()->format('ym');
        
        $latest = AccountTransaction::first();
        $count = $latest ? $latest->id : 0;

        $sequence = sprintf('%06d', $count + 1);

        return  $currentYear . $sequence;
    }
    
    public static function updateTransactionBalance(AccountTransaction $transaction, MemberAccount $account) {
        $transaction->remaining_balance = $account->balance;
        $transaction->saveQuietly();
    }

    public static function computeEarnInterest($principal, $annualInterestRate) {
        // Convert the annual interest rate to a decimal
        // Convert the annual interest rate to a decimal
        $r = ($annualInterestRate / 100) / 365;
        // Calculate the daily earnings
        $dailyEarnings = $principal * $r;
        
        return round($dailyEarnings, 2);
    }
}