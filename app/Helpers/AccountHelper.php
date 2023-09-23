<?php

namespace App\Helpers;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\MemberAccount;

class AccountHelper {
    public static function generateAccount() {
        $timestamp = time();
        $randomNumber = mt_rand(1000, 9999); 

        $member = MemberAccount::orderBy('account_number', 'desc')->first();

        if($member)
            $randomNumber ++;

        return $timestamp . $randomNumber;
    }

    public static function generateUniqueTransactionNumber() {
        // Generate a unique transaction number based on timestamp and a random number
        $timestamp = time();
        $randomNumber = mt_rand(1000, 9999); // You can adjust the range as needed
    
        // Combine the timestamp and random number to create a unique identifier
        $transactionNumber = "TXN" . $timestamp . $randomNumber;
    
        return $transactionNumber;
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