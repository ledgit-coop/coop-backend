<?php

namespace App\Helpers;

use App\Models\AccountTransaction;
use App\Models\MemberAccount;
use Illuminate\Support\Carbon;

class AccountHelper {

    public static function generateAccount() {

        $currentYear = Carbon::now()->format('ym');
        
        $latest = MemberAccount::first();
        $count = $latest ? $latest->id : 0;

        $sequence = sprintf('%09d', $count + 1);

        return  $currentYear . $sequence;
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