<?php

namespace App\Helpers;

use App\Constants\TransactionSubTypes;
use App\Constants\TransactionType;
use App\Models\Loan;
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
}

