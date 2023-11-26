<?php

namespace App\Helpers;

use App\Constants\TransactionSubTypes;
use App\Constants\TransactionType;
use App\Models\Loan;
use App\Models\LoanSchedule;
use App\Models\Member;
use App\Models\Transaction;
use App\Models\TransactionSubType;
use App\Models\User;
use Exception;
use Illuminate\Support\Carbon;

class TransactionHelper {

    public static function generateTransactionNumber() {

        $currentYear = Carbon::now()->format('ymd');
        $latest = Transaction::orderBy('id', 'desc')->withTrashed()->first();
        $count = $latest ? abs($latest->id) : 0;

        $sequence = sprintf('%09d', $count + 1);
        
        return  'TXN-'.$currentYear . $sequence;
    }

    public static function makeTransaction(
        float $amount,
        string $particular,
        $type,
        Carbon $transactionDate,
        $created_by = 'System' ,
        $extraParams = null) : Transaction {

        if(!in_array($type, TransactionType::LIST)) throw new Exception("Transaction type is not supported.", 1);

        return Transaction::create([
            'transaction_number' => self::generateTransactionNumber(),
            'amount' => $amount,
            'type' => $type,
            'transaction_date' => $transactionDate->format('Y-m-d'),
            'particular' => $particular,
            'parameters' => $extraParams ? json_encode($extraParams) : null,
            'created_by' => $created_by,
        ]);
    }

    public static function makeMembershipTransaction(Member $member, Carbon $paidDate, float $amount) {
        $name =  $member->full_name;
        $number = $member->member_number;
        $transaction = self::makeTransaction(
            $amount,
            "Membership payment made by $name - $number",
            TransactionType::REVENUE,
            $paidDate,
            'System',
            [
                'member_id' => $member->id
            ]
        );

        $transaction->transaction_sub_type_id = TransactionSubType::where('key', TransactionSubTypes::MEMBERSHIP_FEE)->firstOrFail()->id;
        $transaction->saveQuietly();

        return $transaction;
    }

    public static function makeOrientationPaymentTransaction(Member $member, Carbon $paidDate, float $amount) {
        $name =  $member->full_name;
        $number = $member->member_number;
        $transaction = self::makeTransaction(
            $amount,
            "Orientation payment made by $name - $number",
            TransactionType::REVENUE,
            $paidDate,
            'System',
            [
                'member_id' => $member->id
            ]
        );
        
        $transaction->transaction_sub_type_id = TransactionSubType::where('key', TransactionSubTypes::ORIENTATION_FEE)->firstOrFail()->id;
        $transaction->saveQuietly();

        return $transaction;
    }

    public static function makeExpenses(string $particular, Carbon $transactionDate, float $amount, User $created_by) {
        return self::makeTransaction(
            $amount,
            $particular,
            TransactionType::EXPENSE,
            $transactionDate,
            $created_by->name,
        );
    }

    public static function makeIncome(string $particular, Carbon $transactionDate, float $amount, User $created_by) {
        return self::makeTransaction(
            $amount,
            $particular,
            TransactionType::REVENUE,
            $transactionDate,
            $created_by->name,
        );
    }
    
    public static function makeLoanPreTerminationFee(Loan $loan, Carbon $transactionDate, float $amount) {
        $date = $transactionDate->format('Y-m-d');
        $loan_number = $loan->loan_number;
        return self::makeTransaction(
            $amount,
            "Loan Pre-Termination - $date/Loan #: $loan_number",
            TransactionType::REVENUE,
            $transactionDate,
            'System',
            [
                "loan_id" => $loan->id,
            ]
        );
    }

    public static function makeLoanAmortizationPayment(LoanSchedule $amortization, User $created_by) {
        $loan = $amortization->loan;
        $product = $loan->loan_product;
        $date = $amortization->due_date->format('Y-m-d');

        $penaltyTransaction = $product->penaltyTransaction;
        $interestTransaction = $product->interestTransaction;
        $principalTransaction = $product->principalTransaction;

        if($principalTransaction) {
            $transaction = self::makeTransaction(
                $amortization->principal_amount,
                "Loan Principal Amortization Payment - $date/Loan #: $loan->loan_number",
                TransactionType::PAYMENT,
                $amortization->due_date,
                $created_by->name,
                [
                    "loan_id" => $loan->id,
                ]
            );

            $transaction->transaction_sub_type_id = $principalTransaction->id;
            $transaction->saveQuietly();

        }

        if($penaltyTransaction) {
            $transaction = self::makeTransaction(
                $amortization->penalty_amount,
                "Loan Penalty Payment - $date/Loan #: $loan->loan_number",
                TransactionType::PAYMENT,
                $amortization->due_date,
                $created_by->name,
                [
                    "loan_id" => $loan->id,
                ]
            );

            $transaction->transaction_sub_type_id = $penaltyTransaction->id;
            $transaction->saveQuietly();
        }

        if($interestTransaction) {
            $transaction = self::makeTransaction(
                $amortization->interest_amount,
                "Loan Interest Payment - $date/Loan #: $loan->loan_number",
                TransactionType::PAYMENT,
                $amortization->due_date,
                $created_by->name,
                [
                    "loan_id" => $loan->id,
                ]
            );

            $transaction->transaction_sub_type_id = $interestTransaction->id;
            $transaction->saveQuietly();
        }

    }
}

