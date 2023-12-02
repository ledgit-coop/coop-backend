<?php

namespace App\Helpers;

use App\Constants\AccountStatus;
use App\Constants\AccountType;
use App\Constants\MemberAccountTransactionType;
use App\Constants\TransactionType;
use App\Models\Account;
use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\MemberAccount;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
            'type' => MemberAccountTransactionType::LOAN_CREDIT,
            'parameters' => [
                "loan_id" => $loan->id
            ]
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
                        'type' => MemberAccountTransactionType::LOAN_FEES,
                        'parameters' => [
                            "loan_id" => $loan->id
                        ]
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
                'type' => MemberAccountTransactionType::LOAN_RELEASED,
                'parameters' => [
                    "loan_id" => $loan->id
                ]
            ]
        ]);
    }

    public static function recordFeeCredits(Loan $loan) {
        foreach ($loan->loan_fees as $fee) {

            if($fee->amount > 0) {

                $template = $fee->loan_fee_template;
                
                if($template->credit_revenue && $template->transaction_sub_type_id) {
                    $transaction = TransactionHelper::makeTransaction(
                        $fee->amount,
                        "Loan Fee (". $fee->loan_fee_template->name . ") / Loan #: $loan->loan_number",
                        TransactionType::REVENUE,
                        $loan->released_date,
                        'System',
                        [
                            "loan_id" => $loan->id,
                        ]
                    );

                    if($template->transaction_sub_type_id) {
                        $transaction->transaction_sub_type_id = $template->transaction_sub_type_id;
                        $transaction->save();
                    }
                }
                // Record share capital
                else if($template->credit_share_capital) {
                    $member_sharecap_acc = $loan->member->share_capital_account;
                    // Ignore if no share cap
                    if($member_sharecap_acc) {
                        $member_sharecap_acc->transactions()->createMany([
                            [
                                'transaction_number' => AccountHelper::generateTransactionNumber(),
                                'particular' => "Share Capital Deposit from Loan ($loan->loan_number)",
                                'transaction_date' => $loan->released_date,
                                'amount' => $fee->amount,
                                'type' => MemberAccountTransactionType::SHARE_CAPITAL
                            ]
                        ]);
                    }
                }
                // Record mortuary
                else if($template->credit_mortuary) {
                    $member = $loan->member;
                    $mortuary_account = $member->mortuary_account;

                    if(!$mortuary_account) {
                        $account = Account::where('type', AccountType::MORTUARY)->first();
                        $mortuary_account = MemberHelper::makeAccount($member, $account, $member->full_name, true);
                    }
                   
                    $mortuary_account->transactions()->createMany([
                        [
                            'transaction_number' => AccountHelper::generateTransactionNumber(),
                            'particular' => "Mortuary Deposit from Loan ($loan->loan_number)",
                            'transaction_date' => $loan->released_date,
                            'amount' => $fee->amount,
                            'type' => MemberAccountTransactionType::MORTUARY
                        ]
                    ]);
                   
                }
                // Record regular savings
                else if($template->credit_regular_savings) {
                    $savings_account = $loan->member->savings_accounts()->where('is_holder_member', true)->first();
                    if($savings_account) {
                        $savings_account->transactions()->createMany([
                            [
                                'transaction_number' => AccountHelper::generateTransactionNumber(),
                                'particular' => "Savings Deposit from Loan ($loan->loan_number)",
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
                'type' => MemberAccountTransactionType::LOAN_PAYMENT,
                'parameters' => [
                    "loan_id" => $loan->id,
                    "loan_schedule_id" => $loanSchedule->id,
                ]
            ]
        ]);
    }

    public static function fixAccounBalance(MemberAccount $account) {
        $transactions = $account->transactions()->orderBy('transaction_date', 'asc')->orderBy('id', 'asc')->get();

        $balance = 0;

        foreach ($transactions as $transaction) {
            $balance = $balance + $transaction->amount;
            $transaction->remaining_balance = $balance;
            $transaction->saveQuietly();
        }

        $account->balance = $balance;
        $account->saveQuietly();

        return $account;
    }

    public static function computeSavingsEarnInterest(Carbon $date) {

        $accounts = MemberAccount::where('status', AccountStatus::ACTIVE)
        ->where('below_maintaining_balance', false)
        ->whereRaw(DB::raw("balance >= maintaining_balance")) // enforce 2nd layer condition
        ->whereHas('transactions', function($transactions) use($date) {
            $transactions->whereRaw(DB::raw("date(transaction_date) <= '". $date->format('Y-m-d') ."'"));
        })
        ->get();

        foreach ($accounts as $account) {
            $interest = AccountHelper::computeEarnInterest($account->balance, $account->earn_interest_per_anum);
            $account->transactions()->createMany([
                [
                    'transaction_number' => AccountHelper::generateTransactionNumber(),
                    'particular' => "Earned interest",
                    'transaction_date' => $date->format('Y-m-d'),
                    'amount' => $interest,
                    'type' => MemberAccountTransactionType::INTEREST_EARNED
                ]
            ]);
        }
    }
}

