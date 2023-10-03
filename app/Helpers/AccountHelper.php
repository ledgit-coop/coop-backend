<?php

namespace App\Helpers;

use App\Models\AccountTransaction;
use App\Models\Member;
use App\Models\MemberAccount;
use Illuminate\Support\Carbon;

class AccountHelper {

    public static function generateAccount(Member $member) {

        $account_count = MemberAccount::orderBy('id', 'desc')->first();
        $account_count = $account_count ? abs($account_count->id) : 0;

        $currentYear = Carbon::now()->format('ym');

        $sequence = sprintf('%06d', $account_count + 1);
       
        return  "ACN-" . $sequence .'-'. $currentYear . + ($account_count + 1);
    }

    public static function generateTransactionNumber() {

        $currentYear = Carbon::now()->format('ym');
        
        $latest = AccountTransaction::orderBy('id', 'desc')->first();
        $count = $latest ? $latest->id : 0;

        $sequence = sprintf('%06d', $count + 1);

        return $currentYear . $sequence . rand(10,50);
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