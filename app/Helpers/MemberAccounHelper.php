<?php

namespace App\Helpers;

use App\Constants\MemberAccountTransactionType;
use App\Constants\TransactionType;
use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\MemberAccount;
use Illuminate\Support\Carbon;

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

        self::recordFeeCredits($loan);
       

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

    public static function recordFeeCredits(Loan $loan) {
        foreach ($loan->loan_fees as $fee) {
            if($fee->amount > 0) {
                $template = $fee->loan_fee_template;

                // Record revenue
                if($template->credit_revenue) {
                    TransactionHelper::makeTransaction(
                        $fee->amount,
                        ($fee->loan_fee_template->name),
                        TransactionType::REVENUE,
                        $loan->released_date,
                        'System',
                        [
                            "loan_id" => $loan->id,
                        ]
                    );
                }

                // Record share capital
                if($template->credit_share_capital) {
                    $member_sharecap_acc = $loan->member->share_capital_account;
                    // Ignore if no share cap
                    if($member_sharecap_acc) {
                        $member_sharecap_acc->transactions()->createMany([
                            [
                                'transaction_number' => AccountHelper::generateTransactionNumber(),
                                'particular' => "Share Capital Deposit from Loan ($loan->loan_number) fee",
                                'transaction_date' => $loan->released_date,
                                'amount' => $fee->amount,
                            ]
                        ]);
                    }
                }
            }
        }
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

    public static function fixAccounBalance(MemberAccount $account) {
        $transactions = $account->transactions()->orderBy('transaction_date', 'asc')->orderBy('id', 'asc')->get();

        $balance = 0;

        foreach ($transactions as $transaction) {
            $balance = $balance + $transaction->amount;
            $transaction->remaining_balance = $balance;
            $transaction->save();
        }

        $account->balance = $balance;
        $account->save();

        return $account;
    }
}

