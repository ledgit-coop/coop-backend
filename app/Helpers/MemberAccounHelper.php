<?php

namespace App\Helpers;

use App\Constants\MemberAccountTransactionType;
use App\Models\Loan;
use App\Models\LoanSchedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class MemberAccounHelper {

    public static function recordLoan(Loan $loan) {

        $account = $loan->member_account;

        $account->transactions()->createMany([
           [
            'transaction_number' => AccountHelper::generateTransactionNumber(),
            'particular' => "Credited Loan - $loan->loan_number",
            'transaction_date' => $loan->released_date,
            'amount' => $loan->principal_amount,
            'type' => MemberAccountTransactionType::LOAN_CREDIT
           ]
        ]);

     
        foreach ($loan->loan_fees as $fee) {
            if($fee->amount > 0) {
                $account->transactions()->createMany([
                    [
                        'transaction_number' => AccountHelper::generateTransactionNumber(),
                        'particular' => "(Fees) " . ($fee->loan_fee_template->name),
                        'transaction_date' => $loan->released_date,
                        'amount' => (-$fee->amount),
                        'type' => MemberAccountTransactionType::LOAN_FEES
                    ]
                ]);
            }
        }
       

        $account->transactions()->createMany([
            [
                'transaction_number' => AccountHelper::generateTransactionNumber(),
                'particular' => "Released Loan - $loan->loan_number",
                'transaction_date' => $loan->released_date,
                'amount' => (-$loan->released_amount),
                'type' => MemberAccountTransactionType::LOAN_RELEASED
            ]
        ]);
    }

    public static function recordPayment(LoanSchedule $loanSchedule, float $paymentAmount, Carbon $payment_date) {
        
        $loan = $loanSchedule->loan;
        $account = $loan->member_account;
        $due_date = $loanSchedule->due_date->format('Y-m-d');

        $account->transactions()->createMany([
            [
                'transaction_number' => AccountHelper::generateTransactionNumber(),
                'particular' => "Loan Payment - ($due_date) $loan->loan_number",
                'transaction_date' => $payment_date,
                'amount' => $paymentAmount,
                'type' => MemberAccountTransactionType::LOAN_PAYMENT
            ]
        ]);
    }
}

